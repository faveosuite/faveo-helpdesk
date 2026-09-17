<?php

namespace Diglactic\Breadcrumbs\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Support\Str;

/**
 * Exception that is thrown if the user attempts to register two breadcrumbs with the same name.
 *
 * @see \Diglactic\Breadcrumbs\ServiceProvider::register()
 */
class DuplicateBreadcrumbException extends BaseException implements ProvidesSolution
{
    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        parent::__construct("Breadcrumb name \"{$name}\" has already been registered");

        $this->name = $name;
    }

    public function getSolution(): Solution
    {
        // Determine the breadcrumbs file name(s)
        $files = (array)config('breadcrumbs.files');

        $basePath = base_path() . DIRECTORY_SEPARATOR;
        foreach ($files as &$file) {
            $file = Str::replaceFirst($basePath, '', $file);
        }

        if (count($files) === 1) {
            $description = "Look in `$files[0]` for multiple breadcrumbs named `{$this->name}`.";
        } else {
            $description = "Look in the following files for multiple breadcrumbs named `{$this->name}`:\n\n- `" . implode("`\n -`", $files) . '`';
        }

        $links = [];
        $links['Defining breadcrumbs'] = 'https://github.com/diglactic/laravel-breadcrumbs#defining-breadcrumbs';
        $links['Laravel Breadcrumbs documentation'] = 'https://github.com/diglactic/laravel-breadcrumbs';

        return BaseSolution::create('Remove the duplicate breadcrumb')
            ->setSolutionDescription($description)
            ->setDocumentationLinks($links);
    }
}
