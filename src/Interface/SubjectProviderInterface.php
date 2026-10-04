<?php

namespace Wexample\SymfonyCheck\Interface;

/**
 * Yields the subjects worth checking in bulk (e.g. every open invoice), so that
 * `check:run` and scheduled jobs can report on them without knowing where they live.
 */
interface SubjectProviderInterface
{
    /**
     * Unique among providers; what `check:run <key>` names it by.
     */
    public function getKey(): string;

    /**
     * @return iterable<object>
     */
    public function getSubjects(): iterable;
}
