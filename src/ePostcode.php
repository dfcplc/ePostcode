<?php namespace Dfcplc\ePostcode;
 
class ePostcode
{

	private static $ws_url = 'http://ws.epostcode.com/uk/postcodeservices13.asmx?WSDL';
	private static $ws_trace = false;
	private static $ws_exception = true;
	private static $ws_cache_wsdl = WSDL_CACHE_NONE;

	public static function lookup_address($account_name, $licence_id, $postcode, $house_number = '', $max_records = 100, $machine_id = '') {

		$return = new \stdClass;
		$return->ok = true;
		$return->error_message = '';
		$return->log_message = null;
		$return->addresses = null;
		$return->response = null;

		$params = array(
			'Postcode' => $postcode,
			'HouseNumber' => $house_number,
			'MaxRecords' => $max_records,
			'AccountName' => $account_name,
			'LicenceID' => $licence_id,
			'MachineID' => $machine_id
		);

		try {

			$client = new \SoapClient(self::$ws_url, array(
				'trace' => self::$ws_trace,
				'exception' => self::$ws_exception,
				'cache_wsdl' => self::$ws_cache_wsdl
			));

			$response = $client->GetPremiseAddressesFromPostcodeAndHouseNumber($params);

			if($response === false || empty($response) || !isset($response->GetPremiseAddressesFromPostcodeAndHouseNumberResult)) {
				$return->ok = false;
				$return->error_message = '';
				$return->log_message = 'Invalid Return';
				$return->response = $response;
				return $return;
			}

			if(isset($response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->IsError) && $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->IsError === true) {
				$return->ok = false;
				$return->error_message = $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->ErrorMessage;
				$return->log_message = '';
				$return->response = $response;
				return $return;
			}

			if(is_array($response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise)) {
				foreach($response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise as $address) {
					$address_details = new \stdClass();

					$address_details->company = trim((string) $address->Organisation);

					if(isset($address->Building_Name) && trim((string) $address->Building_Name)!=="") {
						$address_details->address1 = trim((string) $address->Building_Name).', '.trim((string) $address->Number.' '.(string) $address->Street);
						$address_details->address2 = trim((string) $address->Dependent_Locality);
					} else {
						$address_details->address1 = trim((string) $address->Number.' '.(string) $address->Street);
						$address_details->address2 = trim((string) $address->Dependent_Locality);
					}
					
					$address_details->town = trim((string) $address->Post_Town);
					$address_details->county = trim((string) $address->County_Traditional);
					$address_details->postcode = trim((string) $address->Postcode);

					$return->addresses[] = $address_details;
				}
			} else {
				$address_details = new \stdClass();

				$address_details->company = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Organisation);

				if(isset($response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Building_Name) && trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Building_Name)!=="") {
					$address_details->address1 = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Building_Name).', '.trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Number.' '.(string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Street);
					$address_details->address2 = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Dependent_Locality);
				} else {
					$address_details->address1 = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Number.' '.(string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Street);
					$address_details->address2 = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Dependent_Locality);
				}

				$address_details->town = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Post_Town);
				$address_details->county = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->County_Traditional);
				$address_details->postcode = trim((string) $response->GetPremiseAddressesFromPostcodeAndHouseNumberResult->List->AddressPremise->Postcode);

				$return->addresses[] = $address_details;
			}

			$return->ok = true;
			$return->error_message = '';
			$return->log_message = '';
			$return->response = $response;
			return $return;

		} catch(\Exception $e) {
			$return->ok = false;
			$return->error_message = '';
			$return->log_message = $e->getMessage();
			$return->response = $e;
			return $return;
		}
	}
}