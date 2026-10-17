<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * The Lists Hub: the small repeating sets of content behind the site —
 * stats, pillars, clients, FAQ, network locations and so on.
 *
 * Every one of these tables shares the same shape (id, …fields…, sort_order,
 * is_active, timestamps), so one controller, one model and one pair of views
 * serve all of them. This registry is what makes each list different.
 *
 * Per list:
 *   label, description   what the Lists Hub card says
 *   table                the database table
 *   consumer             a note on where it shows up, displayed in the admin
 *   fields               key => [label, type, required, hint, options]
 *   payload              ordered field keys stored positionally in `payload`
 *   payloadScalar        true when `payload` holds a bare value, not an array
 *
 * Field types:
 *   text, textarea, number, decimal
 *   lines    a JSON array of strings, edited one per line
 *   numbers  a JSON array of numbers, edited comma-separated
 *   select   a fixed choice, see options
 */
class Lists extends BaseConfig
{
    /** @var array<string,array<string,mixed>> */
    public array $lists = [
        // ── Home page ────────────────────────────────────────────────────────
        'stats' => [
            'label'       => 'Stats',
            'description' => 'The headline figures on the home page.',
            'table'       => 'stats',
            'consumer'    => 'Home page, stats band',
            'payload'     => ['value', 'caption'],
            'fields'      => [
                'value'   => ['label' => 'Figure', 'type' => 'text', 'required' => true, 'hint' => 'e.g. 600 Bar'],
                'caption' => ['label' => 'Caption', 'type' => 'text', 'required' => true, 'hint' => 'Use &lt;br&gt; to break the line.'],
            ],
        ],

        'hero_slides' => [
            'label'       => 'Hero Slides',
            'description' => 'The rotating banner at the top of the home page.',
            'table'       => 'hero_slides',
            'consumer'    => 'Home page, hero carousel',
            'fields'      => [
                'image_key'  => ['label' => 'Photo key', 'type' => 'text', 'required' => true, 'hint' => 'e.g. tube-mill/mill-1'],
                'label'      => ['label' => 'Photo caption', 'type' => 'text', 'required' => true],
                'name'       => ['label' => 'Machine name', 'type' => 'text', 'required' => true],
                'metric'     => ['label' => 'Headline metric', 'type' => 'text', 'required' => true],
                'spec'       => ['label' => 'Spec sheet key', 'type' => 'text', 'hint' => 'Links the slide to a machine page.'],
                'title_json' => ['label' => 'Headline', 'type' => 'lines', 'required' => true, 'hint' => 'Exactly three lines. The middle one is highlighted.'],
                'copy'       => ['label' => 'Supporting text', 'type' => 'textarea', 'required' => true],
            ],
        ],

        'pillars' => [
            'label'       => 'Pillars',
            'description' => 'The four capability pillars on the home page.',
            'table'       => 'pillars',
            'consumer'    => 'Home page, "The TIMB difference"',
            'payload'     => ['number', 'title', 'text'],
            'fields'      => [
                'number' => ['label' => 'Number', 'type' => 'text', 'required' => true, 'hint' => 'e.g. 01'],
                'title'  => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'text'   => ['label' => 'Description', 'type' => 'textarea', 'required' => true],
            ],
        ],

        'process_steps' => [
            'label'       => 'Process Steps',
            'description' => 'The manufacturing process timeline.',
            'table'       => 'process_steps',
            'consumer'    => 'Home page, process strip',
            'payload'     => ['number', 'title', 'machines'],
            'fields'      => [
                'number'   => ['label' => 'Number', 'type' => 'text', 'required' => true],
                'title'    => ['label' => 'Title', 'type' => 'text', 'required' => true, 'hint' => 'Use &lt;br&gt; to break the line.'],
                'machines' => ['label' => 'Machines', 'type' => 'text', 'required' => true, 'hint' => 'Separated by · characters.'],
            ],
        ],

        'line_stages' => [
            'label'       => 'Line Stages',
            'description' => 'The connected production line section.',
            'table'       => 'line_stages',
            'consumer'    => 'Home page, "One connected production line"',
            'payload'     => ['number', 'title', 'metric', 'metric_label', 'machines'],
            'fields'      => [
                'number'       => ['label' => 'Number', 'type' => 'text', 'required' => true],
                'title'        => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'metric'       => ['label' => 'Metric', 'type' => 'text', 'required' => true],
                'metric_label' => ['label' => 'Metric caption', 'type' => 'text', 'required' => true],
                'machines'     => ['label' => 'Machines', 'type' => 'lines', 'required' => true, 'hint' => 'One machine name per line.'],
            ],
        ],

        'home_industries' => [
            'label'        => 'Home Industries',
            'description'  => 'The industries strip on the home page.',
            'table'        => 'home_industries',
            'consumer'     => 'Home page, industries strip',
            'payload'      => ['name'],
            'payloadScalar' => true,
            'fields'       => [
                'name' => ['label' => 'Industry', 'type' => 'text', 'required' => true],
            ],
        ],

        // ── Company pages ────────────────────────────────────────────────────
        'industries' => [
            'label'       => 'Industries',
            'description' => 'The industry sectors TIMB serves, with their sub-items.',
            'table'       => 'industries',
            'consumer'    => 'Industries page',
            'fields'      => [
                'slug'               => ['label' => 'Key', 'type' => 'text', 'required' => true, 'hint' => 'Lowercase, hyphens only.'],
                'name'               => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'description'        => ['label' => 'Description', 'type' => 'textarea', 'required' => true],
                'items_json'         => ['label' => 'Sub-items', 'type' => 'lines', 'hint' => 'One per line.'],
                'machine_slugs_json' => ['label' => 'Related machines', 'type' => 'lines', 'hint' => 'One machine slug per line.'],
            ],
        ],

        'projects' => [
            'label'       => 'Projects',
            'description' => 'Completed projects, grouped by machine.',
            'table'       => 'projects',
            'consumer'    => 'Projects page',
            'fields'      => [
                'name'         => ['label' => 'Project', 'type' => 'text', 'required' => true],
                'machine_slug' => ['label' => 'Machine', 'type' => 'text', 'required' => true, 'hint' => 'A machine slug, e.g. tube-mill.'],
            ],
        ],

        'cases' => [
            'label'       => 'Case Studies',
            'description' => 'Longer project write-ups.',
            'table'       => 'cases',
            'consumer'    => 'Projects page',
            'fields'      => [
                'title'       => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'tag'         => ['label' => 'Tag', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true],
            ],
        ],

        'clients' => [
            'label'       => 'Client Work',
            'description' => 'Named clients and what was delivered for them.',
            'table'       => 'clients',
            'consumer'    => 'Clients page',
            'fields'      => [
                'name'           => ['label' => 'Client', 'type' => 'text', 'required' => true],
                'delivered_work' => ['label' => 'Delivered', 'type' => 'text', 'required' => true],
            ],
        ],

        'client_names' => [
            'label'       => 'Client Names',
            'description' => 'The plain list of client names.',
            'table'       => 'client_names',
            'consumer'    => 'Clients page, name strip',
            'fields'      => [
                'name' => ['label' => 'Client name', 'type' => 'text', 'required' => true],
            ],
        ],

        'faq_items' => [
            'label'       => 'FAQ Items',
            'description' => 'Questions and answers.',
            'table'       => 'faq_items',
            'consumer'    => 'Clients page, FAQ section',
            'fields'      => [
                'question' => ['label' => 'Question', 'type' => 'text', 'required' => true],
                'answer'   => ['label' => 'Answer', 'type' => 'textarea', 'required' => true],
            ],
        ],

        'career_areas' => [
            'label'       => 'Career Areas',
            'description' => 'The areas offered in the careers application form.',
            'table'       => 'career_areas',
            'consumer'    => 'Careers page, application form',
            'fields'      => [
                'name' => ['label' => 'Area', 'type' => 'text', 'required' => true],
            ],
        ],

        'network_nodes' => [
            'label'       => 'Network Nodes',
            'description' => 'The locations pinned on the India map.',
            'table'       => 'network_nodes',
            'consumer'    => 'Contact page, India map',
            'fields'      => [
                'name'         => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'type'         => ['label' => 'Type', 'type' => 'select', 'required' => true, 'options' => ['works' => 'Works / Factory', 'hq' => 'Corporate HQ']],
                'place'        => ['label' => 'Place', 'type' => 'text', 'required' => true],
                'sub'          => ['label' => 'Subtitle', 'type' => 'text', 'required' => true],
                'address'      => ['label' => 'Address', 'type' => 'textarea', 'required' => true],
                'phones_json'  => ['label' => 'Phone numbers', 'type' => 'lines', 'hint' => 'One per line.'],
                'lat'          => ['label' => 'Latitude', 'type' => 'decimal', 'required' => true],
                'lon'          => ['label' => 'Longitude', 'type' => 'decimal', 'required' => true],
                'callout_json' => ['label' => 'Label position', 'type' => 'numbers', 'required' => true, 'hint' => 'Two numbers: across %, down %. e.g. 55, 63'],
            ],
        ],

        'disclaimer_points' => [
            'label'       => 'Disclaimer Points',
            'description' => 'The bullet points on the disclaimer page.',
            'table'       => 'disclaimer_points',
            'consumer'    => 'Disclaimer page',
            'fields'      => [
                'body' => ['label' => 'Point', 'type' => 'textarea', 'required' => true],
            ],
        ],
    ];
}
