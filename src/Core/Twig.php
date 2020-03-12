<?php

namespace App\Core;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Extra\Html\HtmlExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Extra\Intl\IntlExtension;
use Twig\TwigFunction;

class Twig
{
    /** @var Environment */
    protected $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(ROOT . '/src/Views/');
        $twig   = new Environment($loader, [
            'debug' => true,
            // 'cache' => ROOT . '/cache/',
            'cache' => false
        ]);

        $twig->addFunction(new TwigFunction('asset', function ($asset) {
            return sprintf('/assets/%s', ltrim($asset, '/'));
        }));

        $twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load($class)
            {
                if (MarkdownRuntime::class === $class) {
                    return new MarkdownRuntime(new DefaultMarkdown());
                }

                return null;
            }
        });
        $twig->addExtension(new MarkdownExtension());
        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new HtmlExtension());

        $this->twig = $twig;
    }

    /**
     * @param $template
     * @param array $array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    function render($template, $array = [])
    {
        echo $this->twig->render($template, $array);
    }
}
