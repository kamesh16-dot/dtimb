<?php

namespace App\Controllers;

/** Company pages: /about /infrastructure /projects /clients /industries /careers. Copy: App\Data\Content. */
class Pages extends BaseController
{
    private function page(string $view, string $title, string $desc, array $extra = []): string
    {
        $site = config('Site');

        return view('pages/' . $view, $extra + [
            'site'     => $site,
            'c'        => require APPPATH . 'Data/Content.php',
            'all'      => array_merge($site->products, $site->solutions),
            'title'    => $title . ' | ' . $site->name,
            'metaDesc' => $desc,
            'preload'  => null,
        ]);
    }

    public function about(): string
    {
        return $this->page('about', 'About', 'TI Machine Building: a unit of Tube Investments of India and the Murugappa Group, building custom machines, automation and tooling since 2012.');
    }

    public function infrastructure(): string
    {
        return $this->page('infrastructure', 'Infrastructure', 'Design tools, assembly, inspection and laboratory facilities behind every TIMB machine.');
    }

    public function projects(): string
    {
        return $this->page('projects', 'Projects', 'Completed projects from TIMB: tube mills, straightening, swaging, test rigs, vision inspection and line automation.');
    }

    public function clients(): string
    {
        return $this->page('clients', 'Clients & FAQ', 'Who TIMB builds for, and answers to common questions about quotations, documents and services.');
    }

    public function industries(): string
    {
        return $this->page('industries', 'Industries', 'Automotive, railways, tube processing, EV, and heavy engineering: where TIMB machines work.');
    }

    public function careers(): string
    {
        return $this->page('careers', 'Careers', 'Work on custom machines, automation and tooling at TI Machine Building.');
    }

    public function news(): string
    {
        return $this->page('news', 'Latest News', 'News from TI Machine Building.');
    }

    public function disclaimer(): string
    {
        return $this->page('disclaimer', 'Disclaimer', 'Website disclaimer for TI Machine Building.');
    }

    public function contact(): string
    {
        return $this->page('contact', 'Contact Us', 'Contact TI Machine Building: works, corporate office, phone numbers and an enquiry form.', [
            'machine' => (string) $this->request->getGet('machine'),
        ]);
    }
}
