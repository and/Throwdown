<?php

//================================================================================

  // Require system level helper libs
  require_once  'vendor/autoload.php';
  require_once  'vendor/modularr/yaml-front-matter/frontmatter.php';

  // Require system level helper functions and configuration files
  require_once  'app/funcs.template.php';
  require_once  'app/config.php';






//================================================================================

  // Initialize a new template helper object(s)
  use \Michelf\MarkdownExtra;

  $markdown   = new MarkdownExtra;
  $mustache   = new Mustache_Engine;
  $whoops     = new Whoops\Run();


  $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());

  // Set Whoops as the default error and exception handler used by PHP:
  $whoops->register();


  // Template fallback globals
  $header_scripts       = [];     // an array of script file URLs that should be loaded in the <head> of a document
  $footer_scripts       = [];     // an array of script file URLs that should be loaded toward the bottom of a document
  $template_file        = '';     // name of the template to be used if provided by a document file
  $page                 = '';     // the given page that should be rendered
  $page_data            = null;     // the given page meta data parsed from
  $page_title_override  = false;  // conditional to determine if we should override the views <title> tag


  // Remove the directory path we don't want
  $request              = $_SERVER['REQUEST_URI'];
  $requestURL           = explode("/", $request);






//================================================================================

  // Get template file names and append them into an array
  // we can use to test template $sage_pages

  $pages        = scandir(DIR_PAGES);
  $safe_pages   = [''];

  // Parse through pages and return page filename
  foreach ($pages as $template => $value) {
    if (!is_dir(DIR_PAGES . $value)) {
      if (!preg_match('/^_/', $value)) {
        $safe_pages[] .= preg_replace('/(.*).md$/', '$1', $value);
      }
    }
  }





//================================================================================

  // IF: keeps users from requesting any file they want
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

  // ELSEIF: are we on an /articles view?
  } elseif ($requestURL[1] === preg_replace("/\//", '', URL_ARTICLES)) {

    $page = DIR_TEMPLATES . "articles.php";


  // ELSE: generate the 404 page
  } else {
    $page = DIR_TEMPLATES . "404.php";
    $page_data = new FrontMatter(DIR_PAGES . '404.md');

  }

//================================================================================


  // Include our defined template
  include($page);


