<?php

namespace App\Services;

use App\Models\Rule;
use App\Models\Transaction;
use App\Models\Watchlist;

class RuleEngineService
{
    /**
     * Evaluate transaction against watchlists and active fraud rules.
     */
    public function evaluate(Transaction $transaction): array
    {
        // 1. فحص القوائم السوداء (حظر فوري)
        $blacklisted = $this->checkBlacklist($transaction);
        if ($blacklisted) {
            return [
                'decision' => 'block',
                'rule_score' => 100.0,
                'matched_rules' => ['Blacklist: ' . $blacklisted->entity_type . ' (' . $blacklisted->entity_value . ')'],
                'force_block' => true,
            ];
        }

        // 2. فحص القوائم البيضاء (تجاوز موثوق)
        if ($this->checkWhitelist($transaction)) {
            return [
                'decision' => 'allow',
                'rule_score' => 0.0,
                'matched_rules' => ['Whitelist Match'],
                'force_block' => false,
            ];
        }

        // 3. تقييم القواعد النشطة
        $rules = Rule::where('is_active', true)->orderBy('priority', 'asc')->get();
        $totalScore = 0.0;
        $matchedRules = [];
        $forcedAction = null;

        foreach ($rules as $rule) {
            if ($this->matchesConditions($rule->conditions, $transaction)) {
                $totalScore += (float) $rule->weight;
                $matchedRules[] = $rule->rule_name;

                if ($rule->action === 'block' && $forcedAction !== 'block') {
                    $forcedAction = 'block';
                } elseif ($rule->action === 'otp' && ! in_array($forcedAction, ['block', 'otp'])) {
                    $forcedAction = 'otp';
                }
            }
        }

        $normalizedScore = min(100.0, $totalScore);

        return [
            'decision' => $forcedAction,
            'rule_score' => $normalizedScore,
            'matched_rules' => $matchedRules,
            'force_block' => ($forcedAction === 'block'),
        ];
    }

    /**
     * Check if transaction touches any blacklisted entry.
     */
    protected function checkBlacklist(Transaction $transaction): ?Watchlist
    {
        return Watchlist::where('list_type', 'blacklist')
            ->where(function ($query) use ($transaction) {
                $query->where(function ($q) use ($transaction) {
                    $q->where('entity_type', 'account')->where('entity_value', $transaction->account_id);
                })->orWhere(function ($q) use ($transaction) {
                    $q->where('entity_type', 'beneficiary')->where('entity_value', $transaction->beneficiary_account);
                })->orWhere(function ($q) use ($transaction) {
                    $q->where('entity_type', 'ip')->where('entity_value', $transaction->ip_address);
                })->orWhere(function ($q) use ($transaction) {
                    $q->where('entity_type', 'device')->where('entity_value', $transaction->device_id);
                });
            })->first();
    }

    /**
     * Check if transaction touches any whitelisted entry.
     */
    protected function checkWhitelist(Transaction $transaction): bool
    {
        return Watchlist::where('list_type', 'whitelist')
            ->where(function ($query) use ($transaction) {
                $query->where(function ($q) use ($transaction) {
                    $q->where('entity_type', 'account')->where('entity_value', $transaction->account_id);
                })->orWhere(function ($q) use ($transaction) {
                    $q->where('entity_type', 'ip')->where('entity_value', $transaction->ip_address);
                });
            })->exists();
    }

    /**
     * Dynamic conditions matching against transaction attributes.
     */
    protected function matchesConditions(array|string|null $conditions, Transaction $transaction): bool
    {
        if (empty($conditions)) {
            return false;
        }

        if (is_string($conditions)) {
            $conditions = json_decode($conditions, true);
        }

        if (! is_array($conditions)) {
            return false;
        }

        foreach ($conditions as $field => $condition) {
            $value = $transaction->{$field} ?? null;

            if (is_array($condition)) {
                $operator = $condition['operator'] ?? '=';
                $target = $condition['value'] ?? null;

                if (! $this->compareValues($value, $operator, $target)) {
                    return false;
                }
            } else {
                if ($value != $condition) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Evaluate operators.
     */
    protected function compareValues(mixed $value, string $operator, mixed $target): bool
    {
        return match ($operator) {
            '>' => $value > $target,
            '>=' => $value >= $target,
            '<' => $value < $target,
            '<=' => $value <= $target,
            '!=' => $value != $target,
            'in' => is_array($target) && in_array($value, $target),
            'not_in' => is_array($target) && ! in_array($value, $target),
            default => $value == $target,
        };
    }
}