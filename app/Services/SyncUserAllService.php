<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAll;

class SyncUserAllService
{
    public function syncFromUser(User $user): UserAll
    {
        return $this->sync([
            'nrp' => $user->nrp,
            'username' => $user->name,
            'position' => $user->position,
            'department' => $user->department,
            'site' => $user->site,
        ]);
    }

    public function sync(array $attributes): UserAll
    {
        $nrp = (string) ($attributes['nrp'] ?? '');

        abort_if($nrp === '', 500, 'NRP is required to sync user_alls.');

        $userAll = UserAll::withTrashed()->firstOrNew([
            'nrp' => $nrp,
        ]);

        $userAll->fill($this->buildPayload($userAll, $attributes, $nrp));

        if ($userAll->trashed()) {
            $userAll->restore();
            $userAll->refresh();
            $userAll->fill($this->buildPayload($userAll, $attributes, $nrp));
        }

        if (! $userAll->exists || $userAll->isDirty()) {
            $userAll->save();
        }

        return $userAll;
    }

    private function buildPayload(UserAll $userAll, array $attributes, string $nrp): array
    {
        return [
            'nrp' => $nrp,
            'username' => $this->resolveRequiredValue(
                $attributes['username'] ?? $attributes['name'] ?? null,
                $userAll->username,
                $nrp
            ),
            'department' => $this->resolveRequiredValue(
                $attributes['department'] ?? null,
                $userAll->department,
                '-'
            ),
            'position' => $this->resolveRequiredValue(
                $attributes['position'] ?? null,
                $userAll->position,
                '-'
            ),
            'email' => $this->resolveNullableValue($attributes['email'] ?? null, $userAll->email),
            'site' => $this->resolveNullableValue($attributes['site'] ?? null, $userAll->site),
        ];
    }

    private function resolveRequiredValue(mixed $incoming, mixed $current, string $fallback): string
    {
        $incoming = $this->normalizeValue($incoming);
        if ($incoming !== null) {
            return $incoming;
        }

        $current = $this->normalizeValue($current);
        if ($current !== null) {
            return $current;
        }

        return $fallback;
    }

    private function resolveNullableValue(mixed $incoming, mixed $current): ?string
    {
        $incoming = $this->normalizeValue($incoming);
        if ($incoming !== null) {
            return $incoming;
        }

        return $this->normalizeValue($current);
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
