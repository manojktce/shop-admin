<?php

namespace App\Models\Concerns;

/**
 * Masters that are in use cannot be deleted (module plan, Module 3).
 * A model lists relation => label pairs in static $usageRelations; later modules add
 * their relations (products, sales, cash movements…) to the masters they reference.
 */
trait GuardsUsage
{
    /** Label of the first non-empty usage relation, or null when the record is unused. */
    public function usedBy(): ?string
    {
        foreach (static::$usageRelations as $relation => $label) {
            if ($this->{$relation}()->exists()) {
                return $label;
            }
        }

        return null;
    }
}
