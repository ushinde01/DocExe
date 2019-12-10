<?php

require_once( PATH_LIBRARIES_PSI . 'CErrorMsg.class.php' );
require_once( PATH_LIBRARIES_PSI . 'CStrings.class.php' );

class CEosFilter {

	const RETURN_CRITERIA_COUNT        = 'count';
	const RETURN_CRITERIA_ARRAY        = 'array';
	const RETURN_CRITERIA_CUSTOM_ARRAY = 'custom_array';
	const RETURN_CRITERIA_OBJECT       = 'object';
	const DEFAULT_PAGE_SIZE            = 100;

	protected $m_arrobjErrorMsgs;

	protected $m_arrstrCustomFields;

	protected $m_intPageNo;
	protected $m_intPageSize;
	protected $m_intReturnSingleRecord;

	protected $m_strReturnCriteria;

	public static $c_arrstrFieldsInfo = [
		'return_criteria'      => [ 'type' => 'str', 'length' => '-1', 'default' => 'object' ],
		'return_single_record' => [ 'type' => 'int', 'length' => '', 'default' => '1' ],
		'page_no'              => [ 'type' => 'int', 'length' => '', 'default' => '0' ],
		'page_size'            => [ 'type' => 'int', 'length' => '', 'default' => 'NULL' ],
		'custom_fields'        => [ 'type' => 'arrstr', 'length' => '-1', 'default' => 'NULL' ],
		'error_msgs'           => [ 'type' => 'arrobj', 'length' => '-1', 'default' => 'NULL' ]
	];

	public function __construct() {

	}

	/**
	 * Get Functions
	 *
	 */

	public function getStringToCamelCase( $strField, $boolCapitalize = true ) {
		$strReturn = str_replace( ' ', '', \Psi\CStringService::singleton()->ucwords( str_replace( '_', ' ', $strField ) ) );

		if( false == $boolCapitalize ) {
			$strReturn = \Psi\CStringService::singleton()->strtolower( $strReturn );
		}

		return $strReturn;
	}

	public function getStringFromCamelCase( $strField ) {
		return \Psi\CStringService::singleton()->strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $strField ) );
	}

	/**
	 * Set Functions
	 *
	 */

	public function addErrorMsg( $objErrorMsg ) {
		$this->m_arrobjErrorMsgs[] = $objErrorMsg;
	}

	public function setValues( $arrmixValues ) {
		$strFilterClassName = get_class( $this );

		$arrstrFieldsInfo = self::$c_arrstrFieldsInfo;

		if( true == valArr( $strFilterClassName::$c_arrstrFieldsInfo ) ) {
			$arrstrFieldsInfo = array_merge( $arrstrFieldsInfo, $strFilterClassName::$c_arrstrFieldsInfo );
		}

		foreach( $arrstrFieldsInfo as $strField => $strFieldInfo ) {
			$strSetFunctionName = 'set' . $this->getStringToCamelCase( $strField );
			if( true == isset( $arrmixValues[$strField] ) ) {
				if( 'str' == $strFieldInfo['type'] ) {
					$this->{$strSetFunctionName}( stripslashes( $arrmixValues[$strField] ) );
				} else {
					$this->{$strSetFunctionName}( $arrmixValues[$strField] );
				}
			}
		}
	}

	public function __call( $strFunctionName, $strFunctionArgument ) {

		$strFilterClassName = get_class( $this );
		$strFieldName       = \Psi\CStringService::singleton()->substr( $strFunctionName, 3 );

		$strFunctionAction  = \Psi\CStringService::singleton()->substr( $strFunctionName, 0, 3 );
		$strValue           = ( true == valArr( $strFunctionArgument ) ) ? current( $strFunctionArgument ) : $strFunctionArgument;
		$strActualFieldName = $this->getStringFromCamelCase( $strFieldName );

		$arrstrFieldsInfo = self::$c_arrstrFieldsInfo;

		if( true == valArr( $strFilterClassName::$c_arrstrFieldsInfo ) ) {
			$arrstrFieldsInfo = array_merge( $arrstrFieldsInfo, $strFilterClassName::$c_arrstrFieldsInfo );
		}

		if( false == array_key_exists( $strActualFieldName, $arrstrFieldsInfo ) ) {
			trigger_error( 'Invalid function name:' . $strActualFieldName, E_USER_ERROR );
		}

		$strFieldName = 'm_' . $arrstrFieldsInfo[$strActualFieldName]['type'] . $strFieldName;

		if( false == property_exists( $strFilterClassName, $strFieldName ) ) {
			trigger_error( 'Invalid property name:' . $strFieldName, E_USER_ERROR );
		}

		switch( $strFunctionAction ) {
			case 'set':
				switch( $arrstrFieldsInfo[$strActualFieldName]['type'] ) {
					case 'int':
						$this->{$strFieldName} = CStrings::strToIntDef( $strValue );
						break;

					case 'flt':
						$this->{$strFieldName} = CStrings::strToFloatDef( $strValue, NULL, false, 2 );
						break;

					case 'str':
						$this->{$strFieldName} = CStrings::strTrimDef( $strValue, $arrstrFieldsInfo[$strActualFieldName]['length'], NULL, true );
						break;

					default:
						$this->{$strFieldName} = $strValue;
						break;
				}
				break;

			case 'sql':

				if( 'int' == $arrstrFieldsInfo[$strActualFieldName]['type'] || 'flt' == $arrstrFieldsInfo[$strActualFieldName]['type'] ) {
					return ( true == isset( $this->{$strFieldName} ) ) ? ( string ) $this->{$strFieldName} : $arrstrFieldsInfo[$strActualFieldName]['default'];
				}

				return ( true == isset( $this->{$strFieldName} ) ) ? '\'' . $this->{$strFieldName} . '\'' : $arrstrFieldsInfo[$strActualFieldName]['default'];

			case 'get':
				return $this->{$strFieldName};

			default:
				trigger_error( 'Invalid function name:' . $strFieldName, E_USER_ERROR );
		}

	}

	/**
	 * Other Functions
	 *
	 */

	public function applyRequestForm( $arrmixRequestForm, $arrmixFormFields = NULL ) {

		if( true == valArr( $arrmixFormFields ) ) {
			$arrmixRequestForm = mergeIntersectArray( $arrmixFormFields, $arrmixRequestForm );
		}

		false == is_null( $this->getRequestData( [ 'page_no' ] ) ) ? $arrmixRequestForm['page_no'] = $this->getRequestData( [ 'page_no' ] ) : $arrmixRequestForm['page_no'] = 1;
		false == is_null( $this->getRequestData( [ 'page_size' ] ) ) ? $arrmixRequestForm['page_size'] = $this->getRequestData( [ 'page_size' ] ) : $arrmixRequestForm['page_size'] = self::DEFAULT_PAGE_SIZE;

		$this->setValues( $arrmixRequestForm );

	}

	public function validate() {
		return true;
	}

	public function getRequestData( $arrstrRequestKeys ) {
		$arrmixValues = $_REQUEST;

		if( true == valArr( $arrstrRequestKeys ) ) {
			foreach( $arrstrRequestKeys as $strRequestKey ) {
				if( true == isset( $arrmixValues[$strRequestKey] ) ) {
					$arrmixValues = $arrmixValues[$strRequestKey];
				} else {
					return NULL;
				}
			}
		}

		return $arrmixValues;
	}

}

?>