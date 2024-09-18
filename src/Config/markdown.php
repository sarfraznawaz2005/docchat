<?php

use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Table\Table;
use Tempest\Highlight\CommonMark\HighlightExtension;

return [
    'code_highlighting' => [
        /*
         * To highlight code, we'll use Shiki under the hood. Make sure it's installed.
         *
         * More info: https://spatie.be/docs/laravel-markdown/v1/installation-setup
         */
        'enabled' => false,

        /*
         * The name of or path to a Shiki theme
         *
         * More info: https://github.com/shikijs/shiki/blob/main/docs/themes.md
         */
        'theme' => 'github-light',
    ],

    /*
     * When enabled, anchor links will be added to all titles
     */
    'add_anchors_to_headings' => false,

    /**
     * When enabled, anchors will be rendered as links.
     */
    'render_anchors_as_links' => false,

    /*
     * These options will be passed to the league/commonmark package which is
     * used under the hood to render markdown.
     *
     * More info: https://spatie.be/docs/laravel-markdown/v1/using-the-blade-component/passing-options-to-commonmark
     */
    'commonmark_options' => [
        'autolink' => [
            'allowed_protocols' => ['https', 'http'], // defaults to ['https', 'http', 'ftp']
            'default_protocol' => 'https', // defaults to 'http'
        ],
        'external_link' => [
            'internal_hosts' => config('app.url'), // TODO: Don't forget to set this!
            'open_in_new_window' => true,
            'html_class' => 'external-link',
            'nofollow' => '',
            'noopener' => 'external',
            'noreferrer' => 'external',
        ],
        'default_attributes' => [
            Strong::class => [
                'class' => 'font-semibold',
            ],
            Heading::class => [
                'class' => static function (Heading $node) {
                    if ($node->getLevel() === 1) {
                        return 'text-lg';
                    } elseif ($node->getLevel() === 2) {
                        return 'text-lg';
                    } elseif ($node->getLevel() === 3) {
                        return 'text-lg';
                    } elseif ($node->getLevel() === 4) {
                        return 'text-lg';
                    } elseif ($node->getLevel() === 5) {
                        return 'text-lg';
                    } elseif ($node->getLevel() === 6) {
                        return 'text-base';
                    } else {
                        return null;
                    }
                },
            ],
            Table::class => [
                'class' => 'table',
            ],
            Link::class => [
                'class' => 'hover:text-blue-700 focus:outline-none',
                'target' => '_blank',
            ],
        ],
        'disallowed_raw_html' => [
            'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'noembed', 'noframes', 'script', 'plaintext'],
        ],
        'footnote' => [
            'backref_class' => 'footnote-backref',
            'backref_symbol' => '↩',
            'container_add_hr' => true,
            'container_class' => 'footnotes',
            'ref_class' => 'footnote-ref',
            'ref_id_prefix' => 'fnref:',
            'footnote_class' => 'footnote',
            'footnote_id_prefix' => 'fn:',
        ],
        'smartpunct' => [
            'double_quote_opener' => '“',
            'double_quote_closer' => '”',
            'single_quote_opener' => '‘',
            'single_quote_closer' => '’',
        ],
    ],

    /*
     * Rendering markdown to HTML can be resource intensive. By default
     * we'll cache the results.
     *
     * You can specify the name of a cache store here. When set to `null`
     * the default cache store will be used. If you do not want to use
     * caching set this value to `false`.
     */
    'cache_store' => null,


    /*
     * When cache_store is enabled, this value will be used to determine
     * how long the cache will be valid. If you set this to `null` the
     * cache will never expire.
     *
     */
    'cache_duration' => null,

    /*
     * This class will convert markdown to HTML
     *
     * You can change this to a class of your own to greatly
     * customize the rendering process
     *
     * More info: https://spatie.be/docs/laravel-markdown/v1/advanced-usage/customizing-the-rendering-process
     */
    'renderer_class' => Spatie\LaravelMarkdown\MarkdownRenderer::class,

    /*
     * These extensions should be added to the markdown environment. A valid
     * extension implements League\CommonMark\Extension\ExtensionInterface
     *
     * More info: https://commonmark.thephpleague.com/2.4/extensions/overview/
     */
    'extensions' => [
        GithubFlavoredMarkdownExtension::class,
        ExternalLinkExtension::class,
        DefaultAttributesExtension::class,
        FootnoteExtension::class,
        FrontMatterExtension::class,
        SmartPunctExtension::class,
        HighlightExtension::class,
    ],

    /*
     * These block renderers should be added to the markdown environment. A valid
     * renderer implements League\CommonMark\Renderer\NodeRendererInterface;
     *
     * More info: https://commonmark.thephpleague.com/2.4/customization/rendering/
     */
    'block_renderers' => [
        // ['class' => FencedCode::class, 'renderer' => MyCustomCodeRenderer::class, 'priority' => 0]
    ],

    /*
     * These inline renderers should be added to the markdown environment. A valid
     * renderer implements League\CommonMark\Renderer\NodeRendererInterface;
     *
     * More info: https://commonmark.thephpleague.com/2.4/customization/rendering/
     */
    'inline_renderers' => [
        // ['class' => FencedCode::class, 'renderer' => MyCustomCodeRenderer::class, 'priority' => 0]
    ],

    /*
     * These inline parsers should be added to the markdown environment. A valid
     * parser implements League\CommonMark\Renderer\InlineParserInterface;
     *
     * More info: https://commonmark.thephpleague.com/2.4/customization/inline-parsing/
     */
    'inline_parsers' => [
        // ['parser' => MyCustomInlineParser::class, 'priority' => 0]
    ],
];
