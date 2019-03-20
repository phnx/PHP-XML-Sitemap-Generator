<?php
/*************************************************************
 PHP Sitemap Generator - Boosted , 2019-03
 Ping Charoenwet

 Additional Abilities:
 	- Robust URL Duplication Check
 	- Only store URLs that has HTTP response code = 200
 	- Skipping Extensions - what's not supposed to be scanned
 	- Page Priority Calculation - xxxxxxx
 	- URL ASCII Escaping Correction

 Still
 "Free to use, without any warranty."

 Based on:
 iProDev PHP XML Sitemap Generator Version 1.0
 Written by iProDev(Hemn Chawroka) http://iprodev.com 28/Mar/2016.

*************************************************************/
	require_once "simple_html_dom.php";

	// Set the output file name.
	$file = "sitemap.xml";

	// Set the start URL. Here is http used, use https:// for 
	// SSL websites.
	$start_url = "https://ks-barcode.com";       

	// Set true or false to define how the script is used.
	// true:  As CLI script.
	// false: As Website script.
	define ('CLI', true);

	// Define here the URLs to skip. All URLs that start with 
	// the defined URL will be skipped too.
	// Example: "http://iprodev.com/print" will also skip
	// http://iprodev.com/print/bootmanager.html
	$skip = array (
					"http://iprodev.com/print/",
				  );

	// Define what file types should be scanned.
	$extension = array (
						 ".html", 
						 ".php",
						 "/",
					   ); 

	// Extensions to skip
	$skipExtension = array (
						 ".rar", 
						 ".zip",
						 ".exe",
					   ); 

	// Last modification
	$lastmod = date("Y-m-d");

	// Scan frequency
	$freq = "daily";

	// Page priority
	$priority = "1.0";

	// Init end ==========================                                        
	define ('NL', CLI ? "\n" : "<br>");

	function rel2abs($rel, $base) {
		if(strpos($rel,"//") === 0) {
			return "http:".$rel;
		}
		/* return if  already absolute URL */
		if  (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
		$first_char = substr ($rel, 0, 1);
		/* queries and  anchors */
		if ($first_char == '#'  || $first_char == '?') return $base.$rel;
		/* parse base URL  and convert to local variables:
		$scheme, $host,  $path */
		extract(parse_url($base));
		/* remove  non-directory element from path */
		$path = preg_replace('#/[^/]*$#',  '', $path);
		/* destroy path if  relative url points to root */
		if ($first_char ==  '/') $path = '';
		/* dirty absolute  URL */
		$abs =  "$host$path/$rel";
		/* replace '//' or  '/./' or '/foo/../' with '/' */
		$re =  array('#(/.?/)#', '#/(?!..)[^/]+/../#');
		for($n=1; $n>0;  $abs=preg_replace($re, '/', $abs, -1, $n)) {}
		/* absolute URL is  ready! */
		return  $scheme.'://'.$abs;
	}

	function GetUrl ($url) {
		$agent = "Mozilla/5.0 (compatible; PHP XML Sitemap Generator - Boosted)";

		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt ($ch, CURLOPT_URL, $url);
		//curl_setopt ($ch, CURLOPT_USERAGENT, $agent);				// enable is needed
		//curl_setopt ($ch, CURLOPT_VERBOSE, 1);					// enable is needed
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt ($ch, CURLOPT_HEADER, TRUE);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);

		$data = curl_exec($ch);

		// additional HTTP code checking
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($httpcode == 200)	// return false if URL has 200 HTTP code
			return $data;

		return false;
	}

	function Scan ($url) {
		global $start_url, $scanned, $pf, $extension, $skip, $freq, $priority, $skipExtension;

		//echo $url . NL;

		$url = filter_var ($url, FILTER_SANITIZE_URL);

		if (!filter_var ($url, FILTER_VALIDATE_URL) || in_array ($url, $scanned)) {
			return;
		}

		$url = trim($url, "/");			// always strip ending slash

		$htmlData = GetUrl ($url);	// check if URL has response code 200

		if($htmlData) {

			// store url before going next
			array_push ($scanned, $url);

			$html = str_get_html ($htmlData);

			try {

				if($html)
					$a1   = $html->find('a');

			} catch(Exception $e) {
				print_r($e);				// stop and traceback when encountering error
				$a1 = array();
			}

			foreach ($a1 as $val) {

				$next_url = $val->href or "";

				// special case: ASCII replacing
				$next_url = str_replace('&#95;', '_', $next_url);
				$next_url = str_replace('&#45;', '-', $next_url);

				$fragment_split = explode ("#", $next_url);
				$next_url       = $fragment_split[0];

				if ((substr ($next_url, 0, 7) != "http://")  && 
					(substr ($next_url, 0, 8) != "https://") &&
					(substr ($next_url, 0, 6) != "ftp://")   &&
					(substr ($next_url, 0, 7) != "mailto:"))
				{
					$next_url = @rel2abs ($next_url, $url);
				}

				$next_url = filter_var ($next_url, FILTER_SANITIZE_URL);
				$next_url = trim($next_url, "/");
				echo 'next url to scan: '.$next_url;

				if (substr ($next_url, 0, strlen ($start_url)) == $start_url) {
					$ignore = false;

					/*
					// not working
					if (!filter_var ($next_url, FILTER_VALIDATE_URL)) {
						$ignore = true;
					}
					*/

					if (in_array ($next_url, $scanned)) {
						$ignore = true;
					}

					if ($next_url == $start_url) {
						$ignore = true;
					}

					if (isset ($skip) && !$ignore) {
						foreach ($skip as $v) {
							if (substr ($next_url, 0, strlen ($v)) == $v)
							{
								$ignore = true;
							}
						}
						foreach ($skipExtension as $v) {
							if (strpos($next_url, $v) !== false)
							{
								$ignore = true;
							}
						}
					}

					echo ' is ignored crawling = '.($ignore ? 'yes' : 'no')."\n";

					if (!$ignore) {
						$continueExtension = false;

						foreach ($extension as $ext) {

							if (strpos ($next_url, $ext) > 0) 
								$continueExtension = true;							

							
						}

						if($continueExtension == true)
							Scan ($next_url);

					}
				
				} else {
					echo ' is ignored crawling = yes'."\n";
				} 

			}
		
		}

	}

	
	$pf = fopen ($file, "w");
	if (!$pf) {
		echo "Cannot create $file!" . NL;
		return;
	}

	$start_url = filter_var ($start_url, FILTER_SANITIZE_URL);

	fwrite ($pf, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
				 "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n" .
				 "        xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n" .
				 "        xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n" .
				 "        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n");

	$scanned = array ();

	$startTime = microtime(true); 

	// recursively crawling & getting URLs
	Scan ($start_url);

	foreach($scanned as $scannedUrl) {

		$pr = 1.0;

		if($scannedUrl != $start_url)
			$pr = number_format ( 
								round ( 
										$priority / 
										count ( explode( 
														"/", trim (
																 str_ireplace ( array ("http://", "https://"), "", 
																				$scannedUrl ),
															 "/" ) )
															 ) + 0.3, 3 ), 1 );

		fwrite ($pf, "  <url>\n" .
					 "    <loc>" . htmlentities ($scannedUrl) ."</loc>\n" .
					 "    <lastmod>".$lastmod."</lastmod>\n" .
					 "    <changefreq>".$freq."</changefreq>\n" .
					 "    <priority>".$pr."</priority>\n" .
					 "  </url>\n");
	}

	fwrite ($pf, "</urlset>\n");
	fclose ($pf);

	echo "Done." . NL;
	echo "$file created." . NL;

	echo 'Total execution time in seconds: ' . (microtime(true) - $startTime) . NL;

	print_r($scanned);

?>