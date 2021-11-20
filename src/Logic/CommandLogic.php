<?php

namespace FuryBee\LoggerUi\Logic;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Descriptor\JsonDescriptor;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class CommandLogic
{
    const IGNORED_OPTIONS = [
        'help',
        'quiet',
        'verbose',
        'version',
        'ansi',
        'no-ansi',
        'no-interaction',
        'env'
    ];

    /**
     * @return Collection
     */
    public static function getAllCommands(): Collection
    {
        $output = new BufferedOutput();

        Artisan::call('list', [
            '--format' => 'json'
        ], $output);

        $commands = json_decode($output->fetch(), true)['commands'];
        $commands = collect($commands);

        $customCommands = self::getCustomCommands();

        if (config('logger-ui.vendor') === false) {
            return $customCommands;
        }

        return $commands
            ->map(function ($commandData) use ($customCommands) {
                $options = collect($commandData['definition']['options'])->reject(function ($option, $optionName) {
                    return in_array($optionName, self::IGNORED_OPTIONS);
                });

                $commandData['definition']['options'] = $options;
                $commandData['is_custom'] = $customCommands->where('name', $commandData['name'])->count() > 0;

                return $commandData;
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * @param string $namespace
     * @return array
     */
    private static function classesInNamespace(string $namespace): array
    {
        $namespace .= '\\';

        return array_filter(get_declared_classes(), function ($item) use ($namespace) {
            return substr($item, 0, strlen($namespace)) === $namespace;
        });
    }


    /**
     * @return Collection
     */
    public static function getCustomCommands(): Collection
    {
        $namespace = 'App\\Console\\Commands';
        $descriptor = new JsonDescriptor();

        return collect(self::classesInNamespace($namespace))
            ->map(function ($className) {
                if (self::isConsoleCommand($className) === true) {
                    return resolve($className);
                }

                return null;
            })
            ->filter()
            ->map(function (Command $command) use ($descriptor) {
                $output = new BufferedOutput();
                $descriptor->describe($output, $command);

                $commandData = json_decode($output->fetch(), true);

                $options = collect($commandData['definition']['options'])->reject(function ($option, $optionName) {
                    return in_array($optionName, self::IGNORED_OPTIONS);
                });

                $commandData['definition']['options'] = $options;
                $commandData['is_custom'] = true;

                return $commandData;
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * @param string $className
     * @return boolean
     */
    public static function isConsoleCommand(string $className): bool
    {
        $parentClass = get_parent_class($className);

        if ($parentClass === false) {
            return false;
        }

        if ($parentClass !== 'Illuminate\Console\Command') {
            return self::isConsoleCommand(get_parent_class($className));
        }

        return true;
    }
}
