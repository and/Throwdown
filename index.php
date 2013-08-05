<?php

  require_once  'app/config.php';

  require_once  'vendor/autoload.php';
  require_once  'vendor/brainsware/php-markdown-extra-extended/markdown_extended.php';
  require_once  'vendor/modularr/yaml-front-matter/frontmatter.php';


//================================================================================


  // Initialize a new Mustache object
  $mustache         = new Mustache_Engine;

  // Template fallback globals
  $footer_scripts   = [];
  $template_file    = '';

  $page             = '';
  $page_data        = [];
  $page_title_override = false;


  // Remove the directory path we don't want
  $request          = $_SERVER['REQUEST_URI'];
  $requestURL       = explode("/", $request);

  // print_r($requestURL);


//================================================================================

  // Get template file names and append them into an array
  // we can use to test template $sage_pages

  $pages      = scandir(DIR_PAGES);
  $safe_pages = [''];

  // Parse through pages and return page filename
  foreach ($pages as $template => $value) {
    if (!is_dir(DIR_PAGES . $value)) {
      if (!preg_match('/^_/', $value)) {
        $safe_pages[] .= preg_replace('/(.*).md$/', '$1', $value);
      }
    }
  }


//================================================================================

  // keeps users from requesting any file they want
  if(in_array($requestURL[1], $safe_pages)) {

    // if the urlrequest is empty, we're on the homepage
    if ($requestURL[1] === '') {
      $requestURL[1] = DEFAULT_HOMEPAGE;
    }

    // get the front-matter meta data
    $request_file   = DIR_PAGES . $requestURL[1] . '.md';
    $page_data      = new FrontMatter($request_file);


    // Determine what template we should load if any
    if ($page_data->keyExists('template')) {
      $template_file  = $page_data->fetch('template') . '.php';
    } else {
      $template_file  = DEFAULT_TEMPLATE;
    }


    // Determine what template we should load based on the URL context
    // and the YAML FrontMatter 'template' key
    switch($requestURL[1]) {

      case '':
        $page = DIR_TEMPLATES . DEFAULT_HOMEPAGE;
        break;

      case $requestURL[1]:
        $page = DIR_TEMPLATES . $template_file;
        break;

      default:
        $page = DIR_TEMPLATES . DEFAULT_HOMEPAGE;
        break;
    }


  // ELSE generate the 404 page
  } else {
    $page = DIR_TEMPLATES . "404.php";
    $page_data = new FrontMatter(DIR_PAGES . '404.md');

  }

//================================================================================


  // Pull in our template helper functions
  require_once  'app/funcs.template.php';

  // Include our defined template
  include($page);

