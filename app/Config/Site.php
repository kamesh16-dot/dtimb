<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Site content. Every fact here is traceable to the TIMB spec sheets;
 * entries marked TODO need confirmation from TIMB before publishing.
 */
class Site extends BaseConfig
{
    public string $name    = 'TIMB';
    public string $tagline = 'TI Machine Building';
    public string $email   = 'timb@tii.murugappa.com';
    public string $linkedin = 'https://www.linkedin.com/in/ti-machine-building-007154191/';
    public string $address = 'Avadi, Chennai – 600 054, India';
    public string $website = 'https://www.timbindia.com';

    /** @var list<array<string,string>> `image` = key in ImageManifest, `spec` = file in public/spec-sheets */
    public array $products = [
        ['slug' => 'tube-mill',        'name' => 'Tube Mill',        'series' => 'TM-Series',       'image' => 'tube-mill/mill-1',                   'spec' => 'tube-mill',        'metric' => '10–254 mm',     'summary' => 'Continuous roll-forming lines for high-precision tube production.'],
        ['slug' => 'draw-bench',       'name' => 'Draw Bench',       'series' => 'TDB-Series',      'image' => 'draw-bench/250-ton-drawbench',       'spec' => 'draw-bench',       'metric' => '5–250 T',       'summary' => 'Motorized chain-type benches for straight-length cold drawing.'],
        ['slug' => 'pilger-mill',      'name' => 'Pilger Mill',      'series' => 'HPTR-Series',     'image' => 'pilger-mill/pilger-1',               'spec' => 'pilger-mill',      'metric' => '±0.03 mm',      'summary' => 'Cold pilgering for superior roundness, finish and accuracy.'],
        ['slug' => 'rotary-swaging',   'name' => 'Rotary Swaging',   'series' => 'TI-Series',       'image' => 'swaging-machine/dual-rotary-swaging','spec' => 'swaging-machine', 'metric' => '5–110 mm',     'summary' => 'Chipless forming that reduces, shapes and tapers metal parts.'],
        ['slug' => 'hydrotester',      'name' => 'Hydrotester',      'series' => 'HT-Series',       'image' => 'hydrotester/hydrotester-kfil',       'spec' => 'hydrotester',      'metric' => 'Up to 600 Bar', 'summary' => 'High-pressure automatic testing for tube and pipe integrity.'],
        ['slug' => 'straightening',    'name' => 'Straightening',    'series' => 'TST-Series',      'image' => 'tube-straightening/straightener-machine', 'spec' => 'tube-straightening', 'metric' => 'Up to 40 m/min', 'summary' => 'Precision tube and bar straightening with independently driven rolls.'],
        ['slug' => 'push-pointing',    'name' => 'Push Pointing',    'series' => 'Hydraulic Series','image' => 'push-pointer/350-ton-push-pointer',  'spec' => 'push-pointer',     'metric' => '35–350 T',      'summary' => 'Heavy-duty end pointing for tubes, rods and cylindrical components.'],
        ['slug' => 'press-entry-line', 'name' => 'Press Entry Line', 'series' => '700-T',           'image' => 'press-entry-line/tidc-entry-line',   'spec' => 'press-entry-line', 'metric' => 'Up to 3000 kg', 'summary' => 'Guarded coil-to-press feed lines for precision fine blanking.'],
    ];

    /**
     * Machines from the previous website that have no photos in this project yet.
     * Product pages use an animated illustration (partials/visuals.php) until
     * an 'image' key from ImageManifest is added.  Content: App\Data\ExtraProductDetails.
     */
    public array $solutions = [
        ['slug' => 'tube-cutting',      'name' => 'Tube Cutting',                'series' => 'Tube Processing',  'image' => null, 'spec' => null, 'metric' => 'Ø15–100 mm',            'visual' => 'cutter'],
        ['slug' => 'robot-automation',  'name' => 'Robot Automation',            'series' => 'Automation',       'image' => null, 'spec' => null, 'metric' => 'Welding · Inspection', 'visual' => 'robot'],
        ['slug' => 'material-handling', 'name' => 'Material Handling',           'series' => 'Other machines',   'image' => null, 'spec' => null, 'metric' => 'Tube bundle handling', 'visual' => 'handling'],
        ['slug' => 'gauging-inspection','name' => 'Gauging & Visual Inspection', 'series' => 'Other machines',   'image' => null, 'spec' => null, 'metric' => 'GRR < 10 %',           'visual' => 'gauge'],
    ];

    /** Header "Products" menu, grouped like the previous website. Values are product/solution slugs. */
    public array $productMenu = [
        'Forming Machines'     => ['tube-mill', 'straightening', 'rotary-swaging', 'push-pointing', 'draw-bench', 'pilger-mill', 'tube-cutting', 'press-entry-line'],
        'Automation Equipment' => ['robot-automation'],
        'Other Machines'       => ['material-handling', 'gauging-inspection', 'hydrotester'],
    ];

    public string $footerTagline = 'From processing to automated inspection, with Industry 4.0 features.';

    /** Hero copy: title = [line 1, accent line, red line]. */
    public array $heroSlides = [
        ['image' => 'tube-mill/mill-1',                    'label' => 'TM-Series Tube Mill', 'name' => 'Tube Mill', 'metric' => '10–254 mm', 'spec' => 'tube-mill',      'title' => ['Engineered for', 'what moves', 'industry.'],  'copy' => 'Precision machinery for tube manufacturing, metal forming and quality-critical production lines—built around your process.'],
        ['image' => 'draw-bench/250-ton-drawbench',        'label' => 'TDB-Series Draw Bench', 'name' => 'Draw Bench', 'metric' => '5–250 T', 'spec' => 'draw-bench',    'title' => ['Built for the', 'pull of', 'precision.'],     'copy' => 'Chain-type cold drawing systems engineered for reliable, high-force production and tight dimensional control.'],
        ['image' => 'pilger-mill/pilger-1',                'label' => 'HPTR-Series Pilger Mill', 'name' => 'Pilger Mill', 'metric' => '±0.03 mm', 'spec' => 'pilger-mill',  'title' => ['Forming the', 'future of', 'seamless.'],      'copy' => 'Advanced cold pilgering technology for critical tubes where surface finish and accuracy cannot be compromised.'],
        ['image' => 'swaging-machine/dual-rotary-swaging', 'label' => 'TI-Series Rotary Swaging', 'name' => 'Rotary Swaging', 'metric' => '5–110 mm', 'spec' => 'swaging-machine', 'title' => ['Zero waste.', 'Maximum', 'form.'],           'copy' => 'Chipless metal forming machines that shape, taper and reduce complex components at speed.'],
    ];

    /**
     * Stats come straight from the spec sheets. The old "70+ years" and "360°"
     * claims were removed (not in any source document).
     * TODO: add a founding-year / years-in-business stat once TIMB confirms it.
     */
    public array $stats = [
        ['8',        'core machine<br>platforms'],
        ['600 Bar',  'maximum hydrotest<br>pressure'],
        ['±0.03 mm', 'cold pilgering<br>accuracy'],
        ['Chennai',  'engineered and built<br>in Avadi, India'],
    ];

    /**
     * "The TIMB difference" capability pillars. Derived only from the process
     * stages and product metrics already published in the spec sheets.
     */
    public array $pillars = [
        ['01', 'Coil handling & forming', 'Entry lines and continuous roll-forming tube mills, 10–254 mm.'],
        ['02', 'Cold drawing & reduction', 'Draw benches 5–250 T, cold pilgering and rotary swaging.'],
        ['03', 'Finishing',                'Straightening up to 40 m/min and push pointing 35–350 T.'],
        ['04', 'Final pressure testing',   'Automatic hydrotesting up to 600 bar.'],
    ];

    public array $process = [
        ['01', 'Coil & strip<br>preparation',      'Entry Lines · Tube Mill'],
        ['02', 'Forming &<br>reduction',            'Tube Mill · Pilger Mill · Swaging'],
        ['03', 'Cold drawing<br>& finishing',       'Push Pointing · Draw Bench · Straightening'],
        ['04', 'Quality<br>assurance',              'Hydrotester'],
    ];

    /**
     * Section 03 "One connected production line". Metrics are the headline
     * figures already published for each machine in the spec sheets.
     * machines = [label, anchor-id] (anchor is a card slug in #machines).
     */
    public array $lineStages = [
        ['01', 'Coil & strip preparation',  'Up to 3000 kg', 'entry line capacity',  ['Entry Lines', 'Tube Mill']],
        ['02', 'Forming & reduction',       '10–254 mm',     'tube mill range',      ['Tube Mill', 'Pilger Mill', 'Swaging']],
        ['03', 'Cold drawing & finishing',  '5–250 T',       'draw bench capacity',  ['Push Pointing', 'Draw Bench', 'Straightening']],
        ['04', 'Quality assurance',         '600 bar',       'hydrotest pressure',   ['Hydrotester']],
    ];

    public array $industries = [
        'Automotive & EV', 'Energy & Oil / Gas', 'Aerospace & Defence',
        'Heat Exchange & HVAC', 'Medical & Precision Components',
    ];

    /**
     * Locations shown on the India map (details from TIMB's own location list).
     * lat/lon are approximate (fine at country scale) – callout = [x%, y%] of the
     * map where the label sits; leader lines are drawn from the pin to it.
     * type: works | hq
     */
    public array $network = [
        [
            'name'    => 'Works / Factory',
            'type'    => 'works',
            'place'   => 'Poochiathipedu, Tiruvallur',
            'sub'     => 'Manufacturing unit',
            'address' => 'TI Machine Building, Plot No. 209, Poochiathipedu Village and Post, Tiruvallur–Red Hills Highway, Tiruvallur, Tamil Nadu – 600052.',
            'phones'  => ['98840 37975', '63820 98415'],
            'lat'     => 13.17,
            'lon'     => 80.02,
            'callout' => [55, 63],
        ],
        [
            'name'    => 'Corporate HQ / Office',
            'type'    => 'hq',
            'place'   => 'Guindy, Chennai',
            'sub'     => 'Tube Investments of India Ltd.',
            'address' => 'Chola Crest, C-54 and C-55, Thiru. Vi. Ka. Industrial Estate, Guindy, Chennai – 600032, Tamil Nadu.',
            'phones'  => ['77080 56523', '63820 98415'],
            'lat'     => 13.007,
            'lon'     => 80.221,
            'callout' => [55, 89],
        ],
    ];
}
