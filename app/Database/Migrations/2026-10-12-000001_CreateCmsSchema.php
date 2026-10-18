<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The CMS schema: every table the site and the admin use.
 *
 * Recreated from the live database (it ran on 2026-10-12 but the file was
 * never added to the project, so a fresh server could not build its tables).
 * It keeps the original version and class name, so a database that already
 * ran it does not run it again, and it creates each table only if missing.
 *
 * Tables are as they stood before the later migrations, which still apply on
 * top: machines.nav_order comes from AddMachineNavOrder.
 */
class CreateCmsSchema extends Migration
{
    public function up(): void
    {
        // ── audit_log
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'entity' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'entity_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('entity');
        $this->forge->createTable('audit_log', true);

        // ── career_applications
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'area' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'experience' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true],
            'message' => ['type' => 'TEXT', 'null' => true],
            'cv_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'new'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('status');
        $this->forge->createTable('career_applications', true);

        // ── career_areas
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('career_areas', true);

        // ── cases
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'tag' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'description' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('cases', true);

        // ── clients
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'delivered_work' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('clients', true);

        // ── client_names
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('client_names', true);

        // ── disclaimer_points
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'body' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('disclaimer_points', true);

        // ── enquiries
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'company' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'interest' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'message' => ['type' => 'TEXT', 'null' => true],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'new'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('status');
        $this->forge->createTable('enquiries', true);

        // ── faq_items
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'question' => ['type' => 'TEXT', 'null' => true],
            'answer' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('faq_items', true);

        // ── hero_slides
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'image_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'metric' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'spec' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'title_json' => ['type' => 'TEXT', 'null' => true],
            'copy' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('hero_slides', true);

        // ── home_industries
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('home_industries', true);

        // ── industries
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'description' => ['type' => 'TEXT', 'null' => true],
            'items_json' => ['type' => 'TEXT', 'null' => true],
            'machine_slugs_json' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('industries', true);

        // ── line_stages
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('line_stages', true);

        // ── machines
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'group_name' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'kind' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'series_label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'visual_key' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'image_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'spec_sheet' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'metric' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'menu_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'show_in_menu' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'is_published' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'details_json' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('menu_order');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('machines', true);

        // ── media
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'legacy_key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'mime' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'size' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'width' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'height' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'alt_text' => ['type' => 'TEXT', 'null' => true],
            'title' => ['type' => 'TEXT', 'null' => true],
            'variants' => ['type' => 'TEXT', 'null' => true],
            'uploaded_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('legacy_key');
        $this->forge->createTable('media', true);

        // ── network_nodes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'type' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false],
            'place' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'sub' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'address' => ['type' => 'TEXT', 'null' => true],
            'phones_json' => ['type' => 'TEXT', 'null' => true],
            'lat' => ['type' => 'DECIMAL', 'constraint' => '10,6', 'null' => false],
            'lon' => ['type' => 'DECIMAL', 'constraint' => '10,6', 'null' => false],
            'callout_json' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('network_nodes', true);

        // ── news_articles
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'published_on' => ['type' => 'DATE', 'null' => true],
            'tag' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'hero_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'hero_alt' => ['type' => 'TEXT', 'null' => true],
            'intro' => ['type' => 'TEXT', 'null' => true],
            'quote' => ['type' => 'TEXT', 'null' => true],
            'body' => ['type' => 'TEXT', 'null' => true],
            'is_published' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('published_on');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('news_articles', true);

        // ── news_gallery
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'article_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'alt' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('article_id');
        $this->forge->createTable('news_gallery', true);

        // ── pages
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'intro' => ['type' => 'TEXT', 'null' => true],
            'body_blocks' => ['type' => 'TEXT', 'null' => true],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'seo_description' => ['type' => 'TEXT', 'null' => true],
            'og_media_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'is_published' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('pages', true);

        // ── pillars
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('pillars', true);

        // ── process_steps
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('process_steps', true);

        // ── projects
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'machine_slug' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('projects', true);

        // ── redirects
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'from_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'to_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'code' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 301],
            'hits' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('from_path');
        $this->forge->createTable('redirects', true);

        // ── settings
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'key' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'value' => ['type' => 'TEXT', 'null' => true],
            'group' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false, 'default' => 'site'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('group');
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('settings', true);

        // ── stats
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sort_order');
        $this->forge->createTable('stats', true);

        // ── users
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'role' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'editor'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'force_password_change' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'last_login_at' => ['type' => 'DATETIME', 'null' => true],
            'failed_logins' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'locked_until' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true);

    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('stats', true);
        $this->forge->dropTable('settings', true);
        $this->forge->dropTable('redirects', true);
        $this->forge->dropTable('projects', true);
        $this->forge->dropTable('process_steps', true);
        $this->forge->dropTable('pillars', true);
        $this->forge->dropTable('pages', true);
        $this->forge->dropTable('news_gallery', true);
        $this->forge->dropTable('news_articles', true);
        $this->forge->dropTable('network_nodes', true);
        $this->forge->dropTable('media', true);
        $this->forge->dropTable('machines', true);
        $this->forge->dropTable('line_stages', true);
        $this->forge->dropTable('industries', true);
        $this->forge->dropTable('home_industries', true);
        $this->forge->dropTable('hero_slides', true);
        $this->forge->dropTable('faq_items', true);
        $this->forge->dropTable('enquiries', true);
        $this->forge->dropTable('disclaimer_points', true);
        $this->forge->dropTable('client_names', true);
        $this->forge->dropTable('clients', true);
        $this->forge->dropTable('cases', true);
        $this->forge->dropTable('career_areas', true);
        $this->forge->dropTable('career_applications', true);
        $this->forge->dropTable('audit_log', true);
    }
}
