<?php

//Copyright (c) 2013 Bernacchia Matteo <kikijikispaccaspecchi@gmail.com>
//
//Permission is hereby granted, free of charge, to any person
//obtaining a copy of this software and associated documentation
//files (the "Software"), to deal in the Software without
//restriction, including without limitation the rights to use,
//copy, modify, merge, publish, distribute, sublicense, and/or sell
//copies of the Software, and to permit persons to whom the
//Software is furnished to do so, subject to the following
//conditions:
//
//The above copyright notice and this permission notice shall be
//included in all copies or substantial portions of the Software.
//
//THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
//OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
//NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
//HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
//WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
//FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
//OTHER DEALINGS IN THE SOFTWARE.

use \Michelf\MarkdownExtra;
use Zend\Cache\StorageFactory;

class Monogatari
{
    private $settings = array();
    private $bindings = array();

    private $cache;
    
    public function __construct()
    {
        $request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
        $index_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';

        $this->readSettings(ROOT_DIR . 'settings.php');
		$this->setLanguage();
        
        $file = null;
        $this->setupUrl($request_url, $index_url, $file);
        $use_cache = $this->initializeCache();    

        if($this->bindings['frontpage'])
        {
            $this->renderPage($file);
        }
        else
        {
			$cache_hit = false;
			$out = 'error';
			
			if($use_cache) $out = $this->cache->getItem($this->getCacheKey($file), $cache_hit);
            
            if($cache_hit) echo $out;
            else $this->renderPage($file);
        }
    }
    
    private function renderPage($file)
    {
        if(file_exists($file)) $page = file_get_contents($file);
        else
        {
            $page = file_get_contents(CONTENT_DIR.'404.md');
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        }
        
        list($metadata, $content) = $this->readContent($page);
        $this->setBinding('metadata', $metadata);
        $this->setBinding('content', $content);
        
        $this->readArchive();

        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('layout/' . $this->settings['layout']);
        $twig = new Twig_Environment($loader, $this->settings['twig_config']);

        $output = $twig->render('index.html', $this->bindings);

        echo $output;

        $this->cache->setItem($this->getCacheKey($file), $output);
    }
    
    private function getCacheKey($path)
    {
        return preg_replace('/[^A-Za-z0-9]/', '-', urlencode($path));
    }
    
    private function readSettings($path)
    {
        require_once($path);
        $this->settings = array_merge($this->settings, $settings);
        
        $this->setBinding('layout_url', $this->settings['base_url'].basename(LAYOUT_DIR).'/'.$this->settings['layout'].'/');
        $this->setBinding('base_dir', rtrim(ROOT_DIR, '/'));
        $this->setBinding('base_url', $this->settings['base_url']);
		$this->setBinding('analytics_ua', $this->settings['analytics_ua']);
    }
    
    private function initializeCache()
    {
		$cache_options = $this->settings['cache_options'];
		
		if(!$cache_options)
			return false;
			
        $this->cache = StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'Filesystem',
                'options' => $cache_options
                ),
            'plugins' => array(
                'exception_handler' => array('throw_exceptions' => true),
            ),
        ));
		
		return true;
    }
    
    private function setupUrl($request_url, $index_url, &$file)
    {
        $url = str_replace('index.php', '', $index_url);
        $frontpage = ($url == $request_url);

        if(substr($request_url, 0, strlen($url)) == $url)
            $url = substr($request_url, strlen($url));

        $url = strtok($url, '?');
        
        if($url) $file = CONTENT_DIR.$url;
        else $file = CONTENT_DIR.'index';
        
        if(is_dir($file)) $file = CONTENT_DIR.$url.'/index.md';
        else $file .= '.md';

        $file = $this->getLocalizedPath($file)[0];

        $this->setBinding('frontpage', $frontpage);
        $this->setBinding('url', $url);
    }
    
    private function parseMetadata($content)
    {
        $delim_begin = '<!---';
        $delim_end = '--!>';
        
        $off_start = strpos($content, $delim_begin) + strlen($delim_begin);
        $off_end = strpos($content, $delim_end);
        
        $header = substr($content, $off_start, $off_end - $off_start);
        $meta = parse_ini_string($header);
        
        if(isset($meta['date']) and $meta['date']) $meta['date_formatted'] = date('Y M jS', strtotime($meta['date']));
        
        return array($meta, $off_end + strlen($delim_end));
    }
    
    private function parseMarkdown()
    {
        $parser = new MarkdownExtra;
        $parser->code_class_prefix = 'prettyprint linenums ';
        $parser->code_attr_on_pre = true;
        
        $ret = array();
        $input = func_get_args();
        
        foreach($input as $string)
        {
            $string = $parser->transform($string);
            $string = SmartyPants($string);
            $ret[] = $string;
        }
        
        return (count($ret)>1 ? $ret : $ret[0]);
    }
    
    private function readContent($content)
    {
        list($metadata, $offset) = $this->parseMetadata($content);
        $content = substr($content, $offset);
        $excerpt = $this->getExcerpt($metadata, $content);
        list($content, $excerpt) = $this->parseMarkdown($content, $excerpt);

        return array($metadata, $content, $excerpt);
    }
    
    private function setLanguage()
    {
		$languages = $this->settings['languages'];
        $lang = $languages[1];
        if(isset($_COOKIE['lang'])) $lang = $_COOKIE['lang'];
        else $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        if($lang != $languages[1]) $lang = $languages[0];
        setcookie('lang', $lang, time()+60*60*24*300, '/');
        
        $this->settings['lang'] = $lang;
        $this->setBinding('lang', $lang);
		$this->setBinding('continue_reading', $this->settings['continue_reading'][$this->settings['lang']]);
		$this->setBinding('site_title', $this->settings['site_title'][$this->settings['lang']]);
    }
    
    private function setBinding($key, $value)
    {
        $this->bindings[$key] = $value;
    }

    private function getPages($base_dir)
    {
        $files = $this->getFiles($base_dir);
		$languages = $this->settings['languages'];
		$pages = array();
        
        foreach($files as $key => $page)
        {
            $base = basename($page);
        
            if($base == '404.md')
            {
                unset($files[$key]);
                continue;
            }
            
            $page_raw = file_get_contents($page);
            list($page_metadata, $page_content, $page_excerpt) = $this->readContent($page_raw);

            $url = str_replace(CONTENT_DIR, $this->settings['base_url'], $page);

            if($this->endsWith($url, 'index.md') ||
               $this->endsWith($url, 'index.'.$languages[0].'.md') ||
               $this->endsWith($url, 'index.'.$languages[1].'.md'))
                continue;
                
            $url = str_replace('.'.$languages[0].'.md', '', $url);
            $url = str_replace('.'.$languages[1].'.md', '', $url);
            $url = str_replace('.md', '', $url);

            $date = '';
            $date_formatted = '';
            
            if(isset($page_metadata['date']))
            {
                $date = $page_metadata['date'];
                $timestamp = strtotime($date);
                
                if($this->settings['lang'] == 'ja')
                {
                    $yo = array('日', '月', '火', '水', '木', '金', '土');;
                    $date_formatted = date('Y年m月j日', $timestamp).' '.$yo[date('w', $timestamp)];
                }
                else
                {
                    $date_formatted = date('Y/m/j D', strtotime($date));
                }
            }

            $data = array(
                'title' => $page_metadata['title'],
                'url' => $url,
                'date' => $date,
                'date_formatted' => $date_formatted,
                'content' => $page_content,
                'excerpt' => $page_excerpt,
            );

            $pages[] = $data;
        }
        
        return $pages;
    }    
    
    private function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
    
    private function getLocalizedPath($path)
    {
        if($this->endsWith($path, 'md'))
        {
            $off = strrpos($path, 'md');
            $path = substr($path, 0, $off);
            $lang = $this->settings['lang'];
            
            if(file_exists($path . $lang . '.md')){return array(($path . $lang . '.md'), $lang);}
            else
            {
				$languages = $this->settings['languages'];
                $next_lang = $lang == $languages[0] ? $languages[1] : $languages[0];
                
                if(file_exists($path . $next_lang . '.md'))
                {
                    return array(($path . $next_lang . '.md'), $next_lang);
                }
                else
                {
                    return array(($path . 'md'), 'neutral');
                }
            }
        }else{return array($path, 'neutral');}
    }

    private function getSections($base_dir)
    {
        $sections = array();
        $sections_unordered = array();
        
        if($handle = opendir($base_dir))
        {
            while($file = readdir($handle))
            {
                $path = $base_dir.$file;
                if($file != '.' && $file != '..' && is_dir($path))
                {
                    list($index_path, $lang) = $this->getLocalizedPath($path.'/index.md');
                    $raw = file_get_contents($index_path);

                    list($meta, $offset) = $this->parseMetadata($raw);
                    
                    if(isset($meta['section_index']))
                    {
                        $sections[] = array('index' => $meta['section_index'], 'dir' => $file, 'name' => $meta['title']);
                    }
                    else
                    {
                        $sections_unordered[] = array('dir' => $file, 'name' => $meta['title']);
                    }
                }
            }
        }
        
        usort($sections, function($a, $b){return $a['index'] > $b['index'];});

        return array_merge($sections, $sections_unordered);
    }
    
    private function getFiles($directory)
    {
        $files = array();
        
        if($handle = opendir($directory))
        {
            while($file = readdir($handle))
            {
                if($file != '.' && $file != '..')
                {
                    if(is_dir($directory. '/' . $file))
                    {
                        $files = array_merge($files, $this->getFiles($directory. '/' . $file));
                    }
                    else
                    {
                        $path = $directory . '/' . $file;
                        if(strstr($path, '.md')) $files[] = $this->getLocalizedPath(preg_replace('/\/\//si', '/', $path))[0];
                    }
                }
            }
            closedir($handle);
        }

        return $files;
    }
    
    private function getExcerpt($metadata, $content)
    {
        if(isset($metadata['excerpt']))
        {
            $length = $metadata['excerpt'];
            $line = strtok($content, PHP_EOL);
            $excerpt = array();
            
            while($line !== false && $length > 0)
            {
                $excerpt[] = $line . PHP_EOL;
                $length = $length-1;
                $line = strtok(PHP_EOL);
            }
            
            return implode($excerpt);
        }
        else return null;
    }    
    
    private function readArchive()
    {
        $sections_list = $this->getSections(CONTENT_DIR);
        
        foreach($sections_list as $section_data)
        {
            $section_dir = $section_data['dir'];
            $section_path = CONTENT_DIR.$section_dir;
            
            $current_section = array('dir'=>$section_dir, 'name'=>$section_data['name']);
            
            $page_list = $this->getPages($section_path);
            $page_urls = array();
            
            foreach($page_list as &$page)
            {
                if(!in_array($page['url'], $page_urls))
                {
                    $current_section['pages'][] = $page;
                    $pages_all[] = $page;
                    $page_urls[] = $page['url'];
                }
            }
            
            $sections[$section_dir] = $current_section;
        }

        $this->setBinding('sections', $sections);
    }
}

?>