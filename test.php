<?php
$data = [
	'gyms'      => [],
	'pokestops' => [],
];

$iitc = [

];

if ( ( $handle = fopen( "iitc-export.csv", "r" ) ) !== false ) {
	while ( ( $data = fgetcsv( $handle, 100000, "," ) ) !== false ) {
		$iitc[$data[1] ] = [
			$data[2] => [
				'name' => $data[0],
				'guid'  => $data[4],
				'lat'  => $data[1],
				'lng'  => $data[2],
				'image'  => $data[3]
			]
		];
	}
	fclose( $handle );
}

// Read the JSON file
$json = file_get_contents( 'g.json' );

// Decode the JSON file
$g    = json_decode( $json, true );
$g    = $g['data']['gyms'];
$json = file_get_contents( 'p.json' );
$p    = json_decode( $json, true );
$p    = $p['data']['pokestops'];
$found = 0;
$notfound = 0;
echo 'GYMS<br />';
function find_data($lat, $lng) {
	global $iitc;
	$lat = (string) $lat;
	$lng = (string) $lng;
	if(array_key_exists($lat,$iitc)){
		if(array_key_exists($lng,$iitc[$lat])){
			return $iitc[$lat][$lng];
		}
	}
	return false;
}
foreach ( $g as $point ) {
	$d = find_data($point['lat'],$point['lon']);
	if($d !== false){
		if($point['name'] == '' || strlen($d['name']) > $point['name']){
			$point['name'] = $d['name'];
		}
		$data['gyms'][$d['guid'] ] = [
			"guid"  => $d['guid'],
			"lat"   => $point['lat'],
			"lng"   => $point['lon'],
			"name"  => $point['name'],
			"image" => $point['url']
		];
		$found++;
		echo $point['name'] . '(' . $point['lat'] . ','.$point['lon'] . ') found<br />';
	} else{
		$notfound++;
		//echo $point['name'] . '(' . $point['lat'] . ','.$point['lon'] . ') not found<br />';
	}

}
echo 'POKESTOPS<br />';

foreach ( $p as $point ) {
	$d = find_data($point['lat'],$point['lon']);
	if($d !== false){
		if($point['name'] == '' || strlen($d['name']) > $point['name']){
			$point['name'] = $d['name'];
		}
		$data['pokestops'][$d['guid'] ] = [
			"guid"  => $d['guid'],
			"lat"   => $point['lat'],
			"lng"   => $point['lon'],
			"name"  => $point['name'],
			"image" => $point['url']
		];
		$found++;
		echo $point['name'] . '(' . $point['lat'] . ','.$point['lon'] . ') found<br />';
	} else{
		$notfound++;
		//echo $point['name'] . '(' . $point['lat'] . ','.$point['lng'] . ') not found<br />';
	}
}
echo 'Found: ' . $found . '<br />';
echo 'Not Found: ' . $notfound . '<br />';
file_put_contents('final-data.json', json_encode($data));