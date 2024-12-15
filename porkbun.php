#!/usr/bin/php -d open_basedir=/usr/syno/bin/ddns
<?php

if ($argc !== 5) {
    echo 'badparam';
    exit();
}
 
$account = (string)$argv[1];
$pwd = (string)$argv[2];
$hostname = (string)$argv[3];
$ip = (string)$argv[4];

// check the hostname contains '.'
if (strpos($hostname, '.') === false) {
    echo "badparam";
    exit();
}

// only for IPv4 format
if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo "badparam";
    exit();
}
function extract_domain($domain)
{
    if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
    {
        return $matches['domain'];
    } else {
        return $domain;
    }
}

function extract_subdomains($domain)
{
    $subdomains = $domain;
    $domain = extract_domain($subdomains);

    $subdomains = rtrim(strstr($subdomains, $domain, true), '.');

    return $subdomains;
}
$subDomain=extract_subdomains($hostname);
$domain=extract_domain($hostname);

$burl = 'https://api.porkbun.com/api/json/v3/dns/';

$url=$burl."retrieveByNameType/".$domain."/A".(strlen($subDomain)>0?"/".$subDomain:"");
$post = array(
	'apikey'=>$account,
	'secretapikey'=>$pwd
);

function setOptions($url,$post){
	
	$options = array(
	  CURLOPT_URL=>$url,
	  CURLOPT_HEADER=>0,
	  CURLOPT_VERBOSE=>0,
	  CURLOPT_RETURNTRANSFER=>true,
	  CURLOPT_HTTPHEADER=>array('Content-Type'=>'application/json'),
	  CURLOPT_USERAGENT=>'Mozilla/4.0 (compatible;)',
	  CURLOPT_POST=>true,
	  CURLOPT_POSTFIELDS=>json_encode($post),
	);
	return $options;
}

function sendPost($url,$post){
	$req = curl_init();
	curl_setopt_array($req, setOptions($url,$post));
	$res = curl_exec($req);
	$json = json_decode($res, true);
	return $json;
}
$json=sendPost($url,$post);
#print_r($json);
    if ("SUCCESS" == $json['status']) {
        #echo 'postOk\n';
		if(count($json['records'])==0){
			
			#echo 'no host found, create a new record\n';
			#create records
			$url=$burl."create/".$domain;
			$post=array(
				'apikey'=>$account,
				'secretapikey'=>$pwd,
				'type'=>'A',
				'ttl'=>'600',
				'content'=>$ip,
				'name'=>$subDomain
			);
			

			$json = sendPost($url,$post);
			#print_r($json);
			if ("SUCCESS" == $json['status']) {
				#echo 'good';
			}else exit();
		}
		else{
			foreach($json['records'] as $rec){
				if($rec['name']==$hostname){
					
					echo "host found, compare ip address";
					if($rec['content']==$ip){
						
						#echo "\nsame ip, exit\n";
						#echo 'good';
					}else{
						#update ip 
						$url=$burl."editByNameType/".$domain."/A".(strlen($subDomain)>0?"/".$subDomain:"");
						$post=array(
							'apikey'=>$account,
							'secretapikey'=>$pwd,
							'ttl'=>'600',
							'content'=>$ip,
						
						);
						$json=sendPost($url,$post);
						if ("SUCCESS" == $json['status']) {
							
							#echo "\nip updated, exit\n";
							#echo 'good';
						}else exit(1);
						
					}
					
				}
			}
		}
		
	}else exit();
 echo 'good';