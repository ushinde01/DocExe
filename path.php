<?php

if( false == defined( 'PATH_DOCUMENT_ROOT' ) ) {
	define( 'PATH_DOCUMENT_ROOT', str_replace( '\\', '/', dirname( __FILE__ ) . '/' ) );

	define( 'PATH_PHP_APPLICATIONS',	PATH_DOCUMENT_ROOT . 'Applications/' );
	define( 'PATH_PHP_EOS',				PATH_DOCUMENT_ROOT . 'Eos/' );
	define( 'PATH_PHP_FRAMEWORKS',		PATH_DOCUMENT_ROOT . 'Frameworks/' );
	define( 'PATH_PHP_INTERFACES',		PATH_DOCUMENT_ROOT . 'Interfaces/' );
	define( 'PATH_PHP_LIBRARIES',		PATH_DOCUMENT_ROOT . 'Libraries/' );
	define( 'PATH_WWW_ROOT',			PATH_DOCUMENT_ROOT . 'www/' );
	define( 'PATH_PHP_PSI',		        PATH_DOCUMENT_ROOT . 'Psi/' );
	define( 'PATH_PHP_TESTSUITE',		PATH_DOCUMENT_ROOT . 'TestSuite/' );
	define( 'PATH_PHP_TESTSUITES',		PATH_DOCUMENT_ROOT . 'TestSuite/SeleniumScripts/Live/' );

	$strTravers 					= '../';
	$strSubEnvironment				= '';
	$strCommonRepositoryPrefix 		= '';
	$strPsCoreConfigFolderName		= 'PsCoreConfig';
	$strMountPsCoreConfigPostfix 	= '';
	$strPsCoreConfigPostfix 		= '';
	$strRelativeBranchPath			= '';
	$strPsLibrariesFolderName 		= 'PsLibraries';
	$strPsPaymentsFolderName 		= 'PsPayments';
	$strPsLibrariesPath				= '../' . $strPsLibrariesFolderName;

	if( false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/ResidentWorks/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/Entrata/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/Standard/ResidentWorks/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/Standard/Entrata/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/rapid/Entrata/' ) ) {
		// if production
		// check track.php file exist or not
		if( false == file_exists( PATH_DOCUMENT_ROOT . $strTravers . $strPsCoreConfigFolderName . '/track.php' ) ) {
			trigger_error( 'track.php failed to load.' . __FILE__, E_USER_ERROR );
		}

		require_once( PATH_DOCUMENT_ROOT . $strTravers . $strPsCoreConfigFolderName . '/track.php' );

		$strSubEnvironment				= 'production';
		$strMountPsCoreConfigPostfix	= CONFIG_CLUSTER_NAME;

	} else if( false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/Qa/' ) ) {
		// if QA
		$strSubEnvironment				= 'qa';
		$strMountPsCoreConfigPostfix	= 'QA';

	} else if( false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/trunk/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/rapidstage/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/rapidproduction/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/standardstage/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/standardproduction/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/branch1/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/branch2/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/branch3/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/branch4/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/PsCoreCodeInspections/' ) || false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/ReportsProject/' ) ) {
		// if stage
		$strMountPsCoreConfigPostfix = 'branches/' . basename( dirname( PATH_DOCUMENT_ROOT ) );
		$strSubEnvironment = 'stage';

		if( false !== strstr( PATH_DOCUMENT_ROOT, '/vhosts/trunk/' ) ) {
			$strSubEnvironment				= 'trunk';
			$strMountPsCoreConfigPostfix	= 'trunk';
		}

	} else if( false !== strstr( PATH_DOCUMENT_ROOT, '/home/likewise-open/DC2/' ) ) {
		// linux local
		$strMountPsCoreConfigPostfix = 'branches/' . basename( dirname( PATH_DOCUMENT_ROOT ) );

		if( false !== strstr( PATH_DOCUMENT_ROOT, '/svn/trunk/' ) ) {
			$strMountPsCoreConfigPostfix	= 'trunk';
		}

		$strSubEnvironment = 'local';

	} else {
		// windows local

		$strTravers = '../../';
		$strCommonRepositoryPrefix = 'PsCore';
		$strMountPsCoreConfigPostfix = 'trunk';

		if( true == strstr( PATH_DOCUMENT_ROOT, 'branches' ) ) {
			// for branches
			$strTravers = '../../../';

			if( true == file_exists( PATH_DOCUMENT_ROOT . $strTravers . $strCommonRepositoryPrefix . 'Common/branches/' . basename( PATH_DOCUMENT_ROOT ) ) ) {
				$strMountPsCoreConfigPostfix = 'branches/' . basename( PATH_DOCUMENT_ROOT );
			}
		}

		$strRelativeBranchPath = $strMountPsCoreConfigPostfix;
		$strRelativeBranchPath .= '/';
		$strPsLibrariesPath = $strTravers . $strPsLibrariesFolderName . '/trunk/';

		$strSubEnvironment = 'local';
	}

	$strMountPsCoreConfigPostfix .= '/';

	// @FIXME: These shouldn't be in Paths defines. Move to CONFIG? - SRS
	if( false == defined( 'CONFIG_CLUSTER_ID' ) ) {
		// for local set cluster_id and cluster name
		if( true == in_array( basename( $strMountPsCoreConfigPostfix ), [ 'standardstage', 'standardproduction' ] ) ) {
			define( 'CONFIG_CLUSTER_ID', 2 );
			define( 'CONFIG_CLUSTER_NAME', 'standard' );
		} else {
			define( 'CONFIG_CLUSTER_ID', 1 );
			define( 'CONFIG_CLUSTER_NAME', 'rapid' );
		}
	}

	// Loading country wise separate config files...
	if( 'production' != $strSubEnvironment ) {

		$arrstrIpAddresses = ( array ) gethostbynamel( php_uname( 'n' ) );

		if( true == isset( $_SERVER['SERVER_ADDR'] ) && '127.0.1.1' != $_SERVER['SERVER_ADDR'] ) {
			$arrstrIpAddresses[] = $_SERVER['SERVER_ADDR'];
		}

		$strCountry = '';

		foreach( $arrstrIpAddresses as $strMyIp ) {
			// IND development IP range.
			foreach( [ '192.168.168.0/21', '192.168.172.0/24', '192.168.186.0/24', '10.18.2.0/24' ] AS $strCidr ) {
				list( $strSubnet, $strMask ) = explode( '/', $strCidr );
				if( true == ( ( ip2long( $strMyIp ) & ~( ( 1 << ( 32 - $strMask ) ) - 1 ) ) == ip2long( $strSubnet ) ) ) {
					$strCountry = 'IND/';
					break 2;
				} else {
					$strCountry = 'US/';
				}
			}
		}
		$strMountPsCoreConfigPostfix = $strCountry . $strMountPsCoreConfigPostfix;
	}

	define( 'PATH_PHP_PAYMENTS_EOS', 							PATH_DOCUMENT_ROOT . $strTravers . $strPsPaymentsFolderName . '/' . $strRelativeBranchPath . 'Eos/' );
	define( 'PATH_PHP_PSLIBRARIES',								PATH_DOCUMENT_ROOT . $strTravers . $strPsLibrariesFolderName . '/' . $strRelativeBranchPath . 'Libraries/' );
	define( 'PATH_PHP_PAYMENTS_TESTSUITE', 						PATH_DOCUMENT_ROOT . $strTravers . $strPsPaymentsFolderName . '/' . $strRelativeBranchPath . 'TestSuite/UnitTests/' );
	define( 'PATH_MIGRATION_DB', 								PATH_DOCUMENT_ROOT . $strTravers . 'MigrationDB/' );
	define( 'PATH_DATA_EXPORT', 								PATH_DOCUMENT_ROOT . $strTravers . 'DataExport/' );
	define( 'PATH_MOUNTS',										PATH_DOCUMENT_ROOT . $strTravers . 'Mounts/' );
	define( 'PATH_VOIP_MOUNTS',									PATH_DOCUMENT_ROOT . $strTravers . 'VoipMounts/' );

	if( true == defined( 'CONFIG_IS_PRODUCTION' ) && 1 == CONFIG_IS_PRODUCTION ) {
		define( 'PATH_MOUNTS_TEST_CLIENTS',                     PATH_DOCUMENT_ROOT . $strTravers . 'test_clients/' );
	} else {
		define( 'PATH_MOUNTS_TEST_CLIENTS',                     PATH_MOUNTS . 'test_clients/' );
	}

	define( 'PATH_MOUNTS_CACHE',								PATH_DOCUMENT_ROOT . $strTravers . 'MountsCache/' );
	define( 'PATH_NON_BACKUP_MOUNTS',							PATH_DOCUMENT_ROOT . $strTravers . 'NonBackupMounts/' );
	define( 'PATH_REALPAGE_DATABASES',							PATH_DOCUMENT_ROOT . $strTravers . 'RealPage_Databases/' );
	define( 'PATH_LOGS',										PATH_DOCUMENT_ROOT . $strTravers . 'Logs/' );
	define( 'PATH_COMPILED_INTERFACES',							PATH_DOCUMENT_ROOT . $strTravers . 'Interfaces/' );
	define( 'PATH_MOUNTS_YIELDSTAR_FEEDS',						PATH_DOCUMENT_ROOT . $strTravers . 'Mounts/yieldstar_feeds/' );
	define( 'PATH_QA_AUTOMATION_SCRIPTS',						PATH_DOCUMENT_ROOT . $strTravers . 'QaAutomationScripts/' );

	define( 'PATH_COMMON',										PATH_DOCUMENT_ROOT . $strTravers . $strCommonRepositoryPrefix . 'Common/' . $strRelativeBranchPath );
	define( 'PATH_CONFIG',										PATH_DOCUMENT_ROOT . $strTravers . $strPsCoreConfigFolderName . '/' . $strRelativeBranchPath );
	define( 'PATH_CONFIG_KEYS',									PATH_CONFIG . 'Keys/' );

	define( 'PATH_PSCOREDBSCRIPTS',								PATH_DOCUMENT_ROOT . $strTravers . 'PsCoreDbScripts/' );
	define( 'PATH_DBSCRIPTS',									PATH_DOCUMENT_ROOT . $strTravers . 'DbScripts/' );
	define( 'PATH_RVSECURESCRIPTS',								PATH_DOCUMENT_ROOT . $strTravers . 'PsResidentVerifySecure/trunk/DbScripts/' );
	define( 'PATH_PROTOTYPES',									PATH_COMMON . 'prototypes/' );

	define( 'PATH_WWW_ROOT_SYSTEM',								PATH_WWW_ROOT . 'System/' );
	define( 'PATH_WWW_ROOT_DEPLOY_TOOL',						PATH_WWW_ROOT_SYSTEM . 'DeployTool/' );
	define( 'PATH_WWW_ROOT_ENTRATA',							PATH_WWW_ROOT . 'Entrata/' );
	define( 'PATH_WWW_ROOT_BROKER_PORTAL',						PATH_WWW_ROOT . 'BrokerPortal/' );

	define( 'PATH_LOGS_EXCEPTIONS',								PATH_LOGS . 'portals/exceptions/' );

	define( 'PATH_COMMON_EMAIL_IMAGES', 						'/images/email_images/' );

	define( 'PATH_COMMON_IMPORT_DATA',							PATH_COMMON . 'entrata_migration_templates/import_data/' );

	define( 'PATH_MIGRATIONS',									'Migrations/' );

	// @TODO: Move MOUNTS paths together for cleanup of this file - SRS
	define( 'PATH_MOUNTS_CHECK21_IMAGES',						PATH_MOUNTS . 'check_21_images/' );

	define( 'PATH_MOUNTS_NACHA_FILES',							PATH_MOUNTS . 'nacha_files/' );
	define( 'PATH_MOUNTS_NACHA_FILES_RETURNS',					PATH_MOUNTS_NACHA_FILES . 'Returns/' );
	define( 'PATH_MOUNTS_NACHA_PARTICIPANTS',					PATH_MOUNTS . 'nacha_participants/' );
	define( 'PATH_MOUNTS_X937_FILES_OUTGOING', 					PATH_MOUNTS . 'x937_files/Outgoing/' );

	define( 'PATH_MOUNTS_DOCUMENTS',							'documents/' );
	define( 'PATH_MOUNTS_LEASES',								'leases/' );
	define( 'PATH_MOUNTS_APPLICATIONS',							'applications/' );
	define( 'PATH_MOUNTS_TEMPLATES',							'templates/' );
	define( 'PATH_MOUNTS_TEMP_DOCUMENTS',						'temp_documents/' );
	define( 'PATH_MOUNTS_DOCUMENTS_PUBLIC',						PATH_MOUNTS . 'documents/' );
	define( 'PATH_MOUNTS_REPORT_TEMPLATES_DOCUMENTS_PUBLIC',	PATH_MOUNTS . 'documents/report_templates/' );
	define( 'PATH_MOUNTS_UTILITY_DOCUMENTS',					'utility_documents/' );

	define( 'PATH_NON_BACKUP_MOUNTS_UTILITY_TRANSACTIONS',		'utility_transactions/' );
	define( 'PATH_NON_BACKUP_MOUNTS_UTILITY_BILLING',			'utility_billing/' );

	define( 'PATH_NON_BACKUP_MOUNTS_UTILITY_INVOICES',			PATH_NON_BACKUP_MOUNTS . 'utility_invoices/' );

	define( 'PATH_MOUNTS_DOCUMENTS_EMPLOYEES',					PATH_MOUNTS_DOCUMENTS_PUBLIC . 'employees/' );
	define( 'PATH_MOUNTS_DOCUMENTS_ADS',						PATH_MOUNTS_DOCUMENTS_PUBLIC . 'ads/' );

	// for inventory audit report
	define( 'PATH_MOUNTS_DOCUMENTS_CLIENT_ADMIN', 				PATH_NON_BACKUP_MOUNTS . 'audit_files/' );

	define( 'PATH_MOUNTS_CUSTOMER_AUTHENTICATIONS',				PATH_NON_BACKUP_MOUNTS . 'customer_authentications/' );

	define( 'PATH_MOUNTS_EXTERNAL_WSDLS',						'external_wsdls/' );
	define( 'PATH_MOUNTS_ACCOUNT_REGIONS',						'account_regions/' );
	define( 'PATH_MOUNTS_VOIP_CUSTOM_FILES',					PATH_VOIP_MOUNTS . 'call_files/voip_custom_files/' );
	define( 'PATH_MOUNTS_INTEGRATION_RESULTS',					'integration_results/' );
	define( 'PATH_MOUNTS_INSURANCE_QUOTE',					    'insurance_quote/' );
	define( 'PATH_NON_BACKUP_MOUNTS_TRANSMISSION',				'transmission/' );
	define( 'PATH_NON_BACKUP_MOUNTS_MIGRATIONS',				PATH_NON_BACKUP_MOUNTS . PATH_MIGRATIONS );

	define( 'PATH_MOUNTS_GLOBAL_UTILITY_TRANSMISSIONS',			PATH_MOUNTS . 'Global/utility_transmissions/' );
	define( 'PATH_MOUNTS_GLOBAL_FAX_DOCUMENT', 					PATH_NON_BACKUP_MOUNTS . 'Global/fax_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_UTILITY_CON_SERVICE_FILES',		PATH_MOUNTS . 'Global/utility_cons_service_files/' );

	define( 'PATH_MOUNTS_CONFIG',								PATH_MOUNTS . $strPsCoreConfigFolderName . '/' . $strMountPsCoreConfigPostfix );

	define( 'PATH_MOUNTS_SCRIPT_HISTORY',						PATH_MOUNTS . 'script_history/' );
	define( 'PATH_MOUNTS_XML',									PATH_MOUNTS . 'xml/' );

	define( 'PATH_MOUNTS_ILS_EMAIL',							'ils_email/' );
	define( 'PATH_MOUNTS_ILS_IMPORTER',							PATH_MOUNTS . 'ils_importer/' );
	define( 'PATH_MOUNTS_ARCHIVED_APPLICATION',					'archived_applications/' );
	define( 'PATH_MOUNTS_SYSTEM_EMAIL',							PATH_MOUNTS . 'system_email/' );
	define( 'PATH_MOUNTS_SYSTEM_EMAIL_ATTACHMENTS',				PATH_MOUNTS . 'system_email_attachments/' );
	define( 'PATH_MOUNTS_CAREER_APPLICATION',					'career_application/' );
	define( 'PATH_MOUNTS_FILES',								'files/' );
	define( 'PATH_MOUNTS_MILITARY',								'military/' );
	define( 'PATH_MOUNTS_USER_FILES',							'user_files/' );
	define( 'PATH_MOUNTS_CONSUMER_DOCS',						'consumer_docs/' );
	define( 'PATH_MOUNTS_RENTAL_COLLECTION_PROOFS',             'rental_collection_proofs/' );
	define( 'PATH_MOUNTS_CORPORATE_DOCS',						'corporate_docs/' );
	define( 'PATH_MOUNTS_CALL_FILES',							'call_files/' );
	define( 'PATH_VOIP_MOUNTS_CALL_FILES',						'call_files/' );
	define( 'PATH_NON_BACKUP_MOUNTS_TEMP_SYSTEM_EMAIL_AWS_S3',	PATH_NON_BACKUP_MOUNTS . 'Global/temp_system_email_aws_s3/' );

	define( 'PATH_MOUNTS_CONFIG_SCRIPTS',						PATH_MOUNTS_CONFIG . 'Scripts/' );
	define( 'PATH_MOUNTS_EMPLOYEES_PHOTOS',						PATH_MOUNTS . 'admin_medias/employees/photos/' );
	define( 'PATH_MOUNTS_EMPLOYEES_NOTIFICATION',				PATH_MOUNTS . 'admin_medias/ps_notifications/' );

	define( 'PATH_MOUNTS_APPS_LOGOS',   						PATH_MOUNTS . 'media_library/apps/' );

	define( 'PATH_MOUNTS_VENDOR_MEDIAS',						PATH_MOUNTS . 'global_medias/vendor_medias/' );

	define( 'PATH_MOUNTS_SURVEY_MEDIAS',						PATH_MOUNTS . 'global_medias/survey_medias/widget/' );

	define( 'PATH_MOUNTS_GLOBAL_MEDIAS_INSURE_MEDIAS',			PATH_MOUNTS . 'global_medias/insure_medias/' );

	define( 'PATH_MOUNTS_GLOBAL_AGREEMENT_TEMPLATES', 			PATH_MOUNTS . 'Global/agreement_templates/' );
	define( 'PATH_MOUNTS_GLOBAL_MASS_EMAIL_ATTACHMENTS',		PATH_MOUNTS . 'Global/mass_email_attachments/' );
	define( 'PATH_MOUNTS_GLOBAL_HELP_RESOURCES',				PATH_MOUNTS . 'Global/help_resources/' );
	define( 'PATH_MOUNTS_GLOBAL_REIMBURSEMENTS',				PATH_MOUNTS . 'Global/reimbursements/' );
	define( 'PATH_MOUNTS_GLOBAL_VENDOR_DOCUMENTS',				PATH_MOUNTS . 'Global/vendor_documents/' );
	define( 'PATH_COMMON_VENDOR_DOCUMENTS',				        PATH_COMMON . 'vendor_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_IMPORT_DATA',					PATH_MOUNTS . 'Global/import_data/' );
	define( 'PATH_MOUNTS_GLOBAL_PRICING_PORTAL',				PATH_MOUNTS . 'Global/pricing_portal/' );
	define( 'PATH_MOUNTS_GLOBAL_ACTIONS',						PATH_MOUNTS . 'Global/actions/' );
	define( 'PATH_MOUNTS_GLOBAL_ILS',							PATH_MOUNTS . 'Global/ils/' );
	define( 'PATH_MOUNTS_GLOBAL_TEMPLATE_DOCUMENTS',			PATH_MOUNTS . 'Global/template_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_UNPROCESSED_UTILITY_DOCUMENTS',	PATH_MOUNTS . 'Global/unprocessed_utility_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_UTILITY_DOCUMENTS',				PATH_MOUNTS . 'Global/utility_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_TRAINING_SESSION_DOCUMENTS',	PATH_MOUNTS . 'Global/training_session_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_CC_CHARGEBACK_FILES',			PATH_MOUNTS . 'Global/cc_chargeback_files/' );
	define( 'PATH_MOUNTS_GLOBAL_AFFORDABLE',					PATH_MOUNTS . 'Global/entrata/affordable/' );
	define( 'PATH_MOUNTS_GLOBAL_HR_DOCS',						PATH_MOUNTS . 'Global/hr_docs/' );
	define( 'PATH_MOUNTS_GLOBAL_OWNER_DOCUMENTS',				PATH_MOUNTS . 'Global/owner_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_PGP_KEYS',						PATH_MOUNTS . 'Global/entrata/pgp_keys/' );

	define( 'PATH_MOUNTS_DOCUMENTS_AFFORDABLE',					PATH_MOUNTS_DOCUMENTS . 'affordable/' );
	define( 'PATH_CLIENTS',										'clients/' );
	define( 'PATH_MOUNTS_CLIENTS',								PATH_MOUNTS . PATH_CLIENTS );
	define( 'PATH_MOUNTS_DOCUMENTS_PS_LEADS',					PATH_MOUNTS_DOCUMENTS_PUBLIC . 'ps_leads/' );
	define( 'PATH_MOUNTS_DOCUMENTS_PS_LEADS_BACKUP_DOCUMENTS',	PATH_MOUNTS_DOCUMENTS_PUBLIC . 'ps_leads/backup_documents/' );
	define( 'PATH_MOUNTS_TV_SLIDES',							PATH_MOUNTS . 'admin_medias/tv_slides/' );

	// @TODO: Inconsistent naming - SRS
	define( 'TV_SLIDES',										'admin_medias/tv_slides/' );
	define( 'PATH_MOUNTS_TECHNICAL_QUESTIONS_SOLUTIONS',		PATH_MOUNTS . 'admin_medias/TechnicalQuestions/Solutions/' );
	define( 'PATH_MOUNTS_TECHNICAL_QUESTIONS_TEST_CASES',		PATH_MOUNTS . 'admin_medias/TechnicalQuestions/TestCases/' );
	define( 'PATH_MOUNTS_GLOBAL_ENTRATA_AFFORDABLE_TIC',		PATH_MOUNTS . 'Global/entrata/affordable/TIC/' );
	define( 'PATH_MOUNTS_GLOBAL_ENTRATA_ACCOUNTING_FORMS',		PATH_MOUNTS . 'Global/entrata/accounting_forms/' );
	define( 'PATH_MOUNTS_GLOBAL_SFTPKEYS',						PATH_MOUNTS . 'Global/sftpkeys/' );
	define( 'PATH_MOUNTS_ADMIN_MEDIAS',							PATH_MOUNTS . 'admin_medias/' );
	define( 'PATH_MOUNTS_DM_DOCUMENTS',							PATH_MOUNTS_DOCUMENTS_PUBLIC . 'dm_documents/' );
	define( 'PATH_MOUNTS_DM_DOCUMENTS_BACKUP_DOCUMENTS',		PATH_MOUNTS_DOCUMENTS_PUBLIC . 'dm_documents/backup_documents/' );
	define( 'PATH_MOUNTS_GLOBAL_RESIDENT_INSURE',				PATH_MOUNTS . 'Global/resident_insure/' );
	define( 'PATH_GAMIFICATION_STORE_PRODUCTS',					'gamification/store_products/' );

	define( 'PATH_NON_BACKUP_MOUNTS_CLIENTS',										PATH_NON_BACKUP_MOUNTS . PATH_CLIENTS );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_WSDLS',									PATH_NON_BACKUP_MOUNTS . 'Global/Wsdls/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_SMS_RELAY_XML',							PATH_NON_BACKUP_MOUNTS . 'Global/sms_relay_xml/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_XML_FILES',								PATH_NON_BACKUP_MOUNTS . 'Global/xml_files/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_YIELDSTAR',								PATH_NON_BACKUP_MOUNTS . 'Global/Yieldstar/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_SPLIT_QUEUES',							PATH_NON_BACKUP_MOUNTS . 'Global/split_queues/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_SQL_LOGS',								PATH_NON_BACKUP_MOUNTS . 'Global/sql_logs/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_RESIDENT_INSURE',						PATH_NON_BACKUP_MOUNTS . 'Global/resident_insure/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_RESIDENTPAY_SCORECARD',					PATH_NON_BACKUP_MOUNTS . 'Global/residentpay_scorecard/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_CACHING',								PATH_NON_BACKUP_MOUNTS . 'Global/caching/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_COUNTERS',								PATH_NON_BACKUP_MOUNTS . 'Global/counters/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_SERVER_MAINTENANCE',						PATH_NON_BACKUP_MOUNTS . 'Global/server_maintenance/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_CUSTOM_TEMPLATES',						PATH_NON_BACKUP_MOUNTS . 'Global/custom_templates/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_WEB_SERVICE_LOGS',						PATH_NON_BACKUP_MOUNTS . 'Global/web_service_logs/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_LEASE_DOCUMENTS',						PATH_NON_BACKUP_MOUNTS . 'Global/lease_documents/' );

	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_DOMAIN_INDEXING',						PATH_NON_BACKUP_MOUNTS . 'Global/domain_indexing/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_EMPLOYEE_STATUS_UPDATES',				PATH_NON_BACKUP_MOUNTS . 'Global/employee_status_updates/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_FAILED_PS_TO_COMPANY_ACCOUNTING_EMAILS',	PATH_NON_BACKUP_MOUNTS . 'Global/failed_ps_to_company_accounting_emails/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_INVOICE_BATCH_STEP',						PATH_NON_BACKUP_MOUNTS . 'Global/invoice_batch_step/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_MAINTENANCE',							PATH_NON_BACKUP_MOUNTS . 'Global/maintenance/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_PS_PRODUCT_MODULES',						PATH_NON_BACKUP_MOUNTS . 'Global/ps_product_modules/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_COMPOSITE_KEY',							PATH_NON_BACKUP_MOUNTS . 'Global/composite_key/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_PS_WEBSITE',								PATH_NON_BACKUP_MOUNTS . 'Global/ps_website/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_DOMAIN_TESTING',							PATH_NON_BACKUP_MOUNTS . 'Global/domain_testing/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_TASK_ATTACHMENTS',						PATH_NON_BACKUP_MOUNTS . 'Global/task_attachments/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_TEMP_TASK_ATTACHMENTS',					PATH_NON_BACKUP_MOUNTS . 'Global/task_attachments/temp_attachments/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_INVOICE_ATTACHMENTS',					PATH_NON_BACKUP_MOUNTS . 'Global/asset_invoices/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_GRAPH_IMAGES',							PATH_NON_BACKUP_MOUNTS . 'Global/graph_images/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_NAGIOS',									PATH_NON_BACKUP_MOUNTS . 'Global/nagios/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_IODATA',									PATH_NON_BACKUP_MOUNTS . 'Global/IOData/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_ACSIODATA',								PATH_NON_BACKUP_MOUNTS . 'Global/ACSIOData/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_JOBS',									PATH_NON_BACKUP_MOUNTS . 'Global/jobs/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_PRICING', 								PATH_NON_BACKUP_MOUNTS . 'Global/Pricing/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_LEASE_EXECUTION_LOGS',					PATH_NON_BACKUP_MOUNTS . 'Global/lease_execution_logs/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_DEPLOYMENTS',							PATH_NON_BACKUP_MOUNTS . 'Global/deployments/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_RELEASE_LOGS',							PATH_NON_BACKUP_MOUNTS_GLOBAL_DEPLOYMENTS . 'release_logs/' );
	define( 'PATH_NON_BACKUP_MOUNTS_SALES_TAX_TRANSMISSIONS',						PATH_NON_BACKUP_MOUNTS . 'sales_tax_transmissions/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_INTEGRATION',							PATH_NON_BACKUP_MOUNTS . 'Global/integration/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_INTEGRATION_PROPERTY_IMPORT',			PATH_NON_BACKUP_MOUNTS . 'Global/integration/property_import/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_DB_BACKUP',								PATH_NON_BACKUP_MOUNTS . 'Global/db_backup/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_SALES_AND_MARKETING',					PATH_NON_BACKUP_MOUNTS . 'Global/sales_and_marketing/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_RESIDENT_VERIFY',						PATH_NON_BACKUP_MOUNTS . 'Global/resident_verify/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_TMP',									PATH_NON_BACKUP_MOUNTS . 'Global/tmp/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_PARSER',									PATH_NON_BACKUP_MOUNTS . 'Global/parser/' );
	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_EMPLOYEE_RESUME',						PATH_NON_BACKUP_MOUNTS_GLOBAL_PS_WEBSITE . 'employee_resumes/' );
	define( 'PATH_NON_BACKUP_MOUNTS_DATABASE_DEPLOYMENT',							PATH_NON_BACKUP_MOUNTS . 'database_deployments/' );
	define( 'PATH_NON_BACKUP_MOUNTS_1099',											PATH_NON_BACKUP_MOUNTS . 'Global/object_storage_temp_files/temp_documents' );

	define( 'PATH_NON_BACKUP_MOUNTS_GLOBAL_REPORT',									PATH_NON_BACKUP_MOUNTS . 'Global/reports/' );

	// @FIXME: These shouldn't be in Paths defines - SRS
	define( 'LOG_APPLICATION_INFO',		true );
	define( 'LOG_APPLICATION_WARNING',	true );
	define( 'LOG_APPLICATION_ERROR',	true );
	define( 'LOG_APPLICATION_DEBUG',	true );
	define( 'LOG_APPLICATION_TRACE',	true );

	// @FIXME: These shouldn't be in Paths defines - SRS
	define( 'CONFIG_SUB_ENVIRONMENT',	$strSubEnvironment );

	define( 'PATH_VHOSTS', '/srv/www/vhosts/' );

	// @TODO: This need be refactored, since the EntrataHa repository path is not as per other repositories.
	define( 'PATH_ENTRATAHA', PATH_VHOSTS . 'EntrataHA/' . ( ( 'production' != $strSubEnvironment ) ? $strSubEnvironment . '/' : '' ) );

}
?>
