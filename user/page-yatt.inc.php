<?php
require_once("lib/YATT/YATT.class.php");

function generate_page($title, $contents)
{
    global $tpl, $template_dir, $domain;

    $page = new YATT($template_dir, 'page.yatt');
    $page->set('domain', $domain);
    $page->set('css_href', css_href());
    $bch = browser_css_href();
    if ($bch) {
	$page->set('browser_css_href', $bch);
	$page->parse('page.bch');
    }
    $page->set('browser_css_href', browser_css_href());
    $page->set('title', $title);
    $page->set('contents', $contents);
    $page->parse('page');

    return $page->output();
}