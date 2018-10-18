<?php

namespace Centreon\Domain\Entity;

class Command
{
    CONST COMMAND_START_IMPEX_WORKER = 'STARTWORKER:1';
    CONST COMMAND_TRANSFER_EXPORT_FILES = 'SENDEXPORTFILE:';

    /**
     * @var string
     */
    private $commandLine;

    /**
     * @return string
     */
    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    /**
     * @param string $commandLine
     */
    public function setCommandLine(string $commandLine): void
    {
        $this->commandLine = $commandLine;
    }

}
