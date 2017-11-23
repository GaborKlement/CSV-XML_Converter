<?php

if (isset($_POST['submit'])) {
	
	$tmpfile = $_FILES["file"]["tmp_name"];
	
	//Creates XML string and XML document using the DOM 
	$xml = new DomDocument('1.0', "UTF-8"); 

	$root = $xml->createElement("supplier-data");
	$root_attr3 = $xml->createAttribute('xmlns');
	$root_attr3->value = "http://www.smarttech.at/seso-masterdata-exchange";
	$root->appendChild($root_attr3);
	$root_attr4 = $xml->createAttribute('xmlns:ns2');
	$root_attr4->value = "http://www.ebutilities.at/datenplattform/0310";
	$root->appendChild($root_attr4);
	$root_attr2 = $xml->createAttribute('id');
	$root_attr2->value = "AT112350";
	$root->appendChild($root_attr2);
	$root_attr1 = $xml->createAttribute('sector');
	$root_attr1->value = "01";
	$root->appendChild($root_attr1);

	if (($handle = fopen($tmpfile, "r")) !== FALSE) {
		$i = 1;
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			
			if ($i > 4) {
				
				$j = $i-4;
				$id = sprintf("%04d", $j);
				
				$installation = $xml->createElement('installation');
				$address = $xml->createElement('address'); 
				$postalcode = $xml->createElement('ns2:PostalCode'); 
				$city = $xml->createElement('ns2:City'); 
				$street = $xml->createElement('ns2:Street');
				$streetno = $xml->createElement('ns2:StreetNo');
				$mpoint = $xml->createElement('meteringPoint');
				$mpoint_attr = $xml->createAttribute('id');

				/*-------------------------------------------------------*/
				/* Mérőpont azonosító */
				$mpoint_attr->value = $data[1];
				/*-------------------------------------------------------*/

				$mpoint->appendChild($mpoint_attr);
				$edir = $xml->createElement('energyDirection');
				$customer = $xml->createElement('customer');
				$name1 = $xml->createElement('ns2:Name1');
				$poadata = $xml->createElement('poaData');
				$poadata_attr = $xml->createAttribute('POANumber');

				/*------------------------------------------*/
				/* POA Number */
				$poadata_attr->value = "AT112350SONDERVOLLMACHTWIEN".$id;
				/*------------------------------------------*/

				$poadata->appendChild($poadata_attr);
				$poasub = $xml->createElement('POASubstantiationData');
				$poasub_attr = $xml->createAttribute('POAProcess');
				$poasub_attr->value = "1";
				$poasub->appendChild($poasub_attr);
				$poasubdata = $xml->createElement('ns2:POASubstantiation');
				$operator = $xml->createElement('gridOperatorId');

				$xml->appendChild( $root );
				$root->appendChild( $installation );
				$installation->appendChild( $address );
				$address->appendChild( $postalcode );
				$address->appendChild( $city );
				$address->appendChild( $street );
				$address->appendChild( $streetno );

				/*----------------------------------------------------*/
				/* Cím */
				$postalcode->nodeValue = $data[9];
				$city->nodeValue = $data[10];
				$street->nodeValue = $data[6];
				if ($data[7] != "") { $streetno->nodeValue = $data[7]; }
				else { $streetno->nodeValue = "0"; }
				/*----------------------------------------------------*/

				$installation->appendChild( $mpoint );
				$mpoint->appendChild( $edir );
				$edir->nodeValue = "CONSUMPTION";
				$mpoint->appendChild( $customer );
				$customer->appendChild( $name1 );

				/*------------------------------------------*/
				/* Név */
				if ($data[5] != "") { 
					$data[5] = str_replace('&', '&amp;', $data[5]);
					$data[5] = str_replace('<', ' ', $data[5]);
					$data[5] = str_replace('>', ' ', $data[5]);
					$name2 = $xml->createElement('ns2:Name2');
					$customer->appendChild( $name2 );
					$name2->nodeValue = $data[5];
				}
				
				$data[4] = str_replace('&', '&amp;', $data[4]);
				$data[4] = str_replace('<', ' ', $data[4]);
				$data[4] = str_replace('>', ' ', $data[4]);
				$name1->nodeValue = $data[4];
				/*------------------------------------------*/

				$customer->appendChild( $poadata );
				$poadata->appendChild( $poasub );
				$poasub->appendChild( $poasubdata );
				$mpoint->appendChild( $operator );

				/*---------------------------------------------------------------------------------------------------------------------------------------------*/
				/* Egyéb információ	*/
				$poasubdata->nodeValue = "Sondervollmacht Wien KundeNr: ".$data[2]." VertragsNr: ".$data[3];
				$operator->nodeValue = "AT001000";
				/*---------------------------------------------------------------------------------------------------------------------------------------------*/

			}
			$i++;
		}
		fclose($handle);
	}

	$t = time();

	$xml->formatOutput;
	$output = "ssondervollmacht_".$t.".xml";
	$xml->save($output);
	
	if (file_exists($output)) {
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: Binary'); 
		header('Content-disposition: attachment; filename="sondervollmacht_'.$t.'.xml"'); 
		readfile($output);
		exit;
	}
}

?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>CSV-XML Konverter</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
		<link rel="stylesheet" href="sky-forms.css">

	</head>
	
	<body bgcolor="#eeeeee">
		<div style="max-width: 600px; margin: 30px auto;">		
			<form action="index.php" method="post" enctype="multipart/form-data" id="sky-form" class="sky-form" novalidate>
				<header>CSV-XML Konverter</header>
					
				<fieldset>					
					
					<section>
						<label for="file" class="input input-file">
							<div class="button"><input name="file" multiple onchange="this.parentNode.nextSibling.value = this.value" type="file">Durchsuchen</div><input placeholder="CSV (UTF-8 durch Trennzeichen getrennt)" readonly type="text">
						</label>
					</section>
					
				</fieldset>
				
				<footer>
					<button type="submit" class="button" name="submit">Senden</button>
				</footer>		
						
			</form>			
		</div>
</body>
</html>