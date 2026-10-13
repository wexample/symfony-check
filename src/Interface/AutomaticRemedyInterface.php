<?php

namespace Wexample\SymfonyCheck\Interface;

/**
 * A remedy the application carries out by itself, when the gate closes, with
 * no one to ask. Whether it lifts the findings at once or later is its own
 * business: the gate stays closed until a check says otherwise.
 */
interface AutomaticRemedyInterface extends RemedyInterface
{
    public function apply(): void;
}
