<?php

//declare(strict_types=1); multiple issues
/**
 * Ceon URI Mapping Installation Check Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingInstallationCheck.php 2025-01-08 torvista
 */

/**
 * Load in the Ceon URI Mapping Version class so it can be extended
 */
require_once(DIR_WS_CLASSES . 'class.CeonURIMappingVersion.php');


// {{{ CeonURIMappingInstallationCheck

/**
 * Examines the store/server's settings and warns the user of any issues. Builds a minimal RewriteRule which should
 * hopefully work with the store, as well as a more comprehensive RewriteRule which should provide better
 * integration with other directories that share the store's directory. Checks all the files used by the software
 * to make sure that all core file modifications are in place.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingInstallationCheck extends CeonURIMappingVersion
{
	// {{{ properties

	/**
	 * Maintains a list of any errors found with the store's configuration files.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_config_files_error_messages = [];

	/**
	 * Maintains a list of any errors found with the core files which should have been modified for Ceon URI
	 * Mapping.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_modified_core_files_error_messages = [];

	/**
	 * Maintains a list of any errors found because files for an old version of Ceon URI Mapping remain in the
	 * store's filesystem.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_old_files_dirs_error_messages = [];

	/**
	 * Holds the basic example rewrite rule for the store, as built by this instance.
	 *
	 * @var     string
	 * @access  protected
	 */
	protected string $_basic_rewrite_rule = '';

	/**
	 * Holds and example rewrite rule for the store, as built by this instance, which attempts to exclude all
	 * custom folders the store is using.
	 *
	 * @var     string
	 * @access  protected
	 */
	protected string $_guestimated_rewrite_rule = '';

	// }}}


	// {{{ Class Constructor

	/**
	 * Creates a new instance of the class.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();

		if (!$this->_lookUpInstalledVersion()) {
			// Error occurred when attempting to get the version number
		}

		if (!$this->_lookUpAutomaticVersionCheckingEnabled()) {
			// Error occurred when attempting to get the version number
		}
	}

	// }}}


	// {{{ _lookUpAutomaticVersionCheckingEnabled()

	/**
	 * Checks if the automatic version checking functionality is enabled and stores the status in this instance's
	 * property.
	 *
	 * @access  protected
	 * @return  bool   True if the check completed without failure, false otherwise. The module not being
	 *                    installed yet is not counted as a failure.
	 */
	protected function _lookUpAutomaticVersionCheckingEnabled(): bool
    {
		global $db;

		$table_exists_query = 'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPING_CONFIGS . '";';
		$table_exists_result = $db->Execute($table_exists_query);

		if (!$table_exists_result->EOF) {
			// Database table exists, get version info

			// Only one config currently supported so its ID is hard-coded in the following SQL
			$automatic_version_checking_sql = '
				SELECT
					automatic_version_checking
				FROM
					' . TABLE_CEON_URI_MAPPING_CONFIGS . '
				WHERE
					id = 1';

			$automatic_version_checking_result = $db->Execute($automatic_version_checking_sql);

			if (!$automatic_version_checking_result->EOF) {
				$this->_automatic_version_checking = $automatic_version_checking_result->fields
					['automatic_version_checking'] == 1;
			}
		}

		return true;
	}

	// }}}


	// {{{ performChecks()

	/**
	 * Performs the installation checks, building a list of messages in the appropriate properties of this
	 * instance, to be used to build the output later.
	 *
	 * @access  public
	 * @return  void
	 */
	public function performChecks(): void
    {
		// Examine the current installation's configure.php files' settings
		$this->_checkConfigureFilesSettings();

		$this->_checkModifiedCoreFiles();

		$this->_checkOldFilesDirsRemaining();
	}

	// }}}


	// {{{ _checkConfigureFilesSettings()

	/**
	 * Examines the store's configure files to check for common mistakes. Adds the appropriate error messages to
	 * the main error messages property.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _checkConfigureFilesSettings(): void
    {
		// Check the admin's configure settings first... they'll already be defined as this is called
		// from within the admin
		if (preg_match('|^https?://[a-z0-9\-\.]+/.+$|i', HTTP_CATALOG_SERVER)) {
			$this->_config_files_error_messages[] = [
				'initial_desc' =>
					sprintf(TEXT_ERROR_VALUE_FOR_ADMIN_SERVER_VARIABLE, 'HTTP_CATALOG_SERVER'),
				'current_value' => [
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'HTTP_CATALOG_SERVER',
						HTTP_CATALOG_SERVER),
                ],
				'extra_desc' => [
					sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_USE_SUBDIRECTORY, 'HTTP_CATALOG_SERVER'),
					sprintf(TEXT_HTTP_SERVER_VALUE_MUST_BE, 'HTTP_CATALOG_SERVER'),
					TEXT_HTTP_SERVER_FORMAT_EXAMPLE
                ],
				'instructions' => [
					sprintf(TEXT_REMOVE_SUBDIRECTORY_FROM_END, 'HTTP_CATALOG_SERVER'),
					sprintf(TEXT_SUBDIRECTORY_SETTINGS_INFO, 'HTTP_CATALOG_SERVER')
                ]
            ];
		} elseif (preg_match('|/$|i', HTTP_CATALOG_SERVER)) {
			$this->_config_files_error_messages[] = [
				'initial_desc' =>
					sprintf(TEXT_ERROR_VALUE_FOR_ADMIN_SERVER_VARIABLE, 'HTTP_CATALOG_SERVER'),
				'current_value' => [
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'HTTP_CATALOG_SERVER',
						HTTP_CATALOG_SERVER),
                ],
				'extra_desc' => [
					sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_HAVE_A_SLASH_AT_END,
						'HTTP_CATALOG_SERVER')
                ],
				'instructions' => [
					sprintf(TEXT_REMOVE_SLASH_FROM_END, 'HTTP_CATALOG_SERVER')
                ]
            ];
		}

		if (preg_match('|^https://[a-z0-9\-\.]+/.+$|i', HTTPS_CATALOG_SERVER)) {
			$this->_config_files_error_messages[] = [
				'initial_desc' =>
					sprintf(TEXT_ERROR_VALUE_FOR_ADMIN_SERVER_VARIABLE, 'HTTPS_CATALOG_SERVER'),
				'current_value' => [
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE,
						'HTTPS_CATALOG_SERVER', HTTPS_CATALOG_SERVER),
                ],
				'extra_desc' => [
					sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_USE_SUBDIRECTORY, 'HTTPS_CATALOG_SERVER'),
					sprintf(TEXT_HTTPS_SERVER_VALUE_MUST_BE, 'HTTPS_CATALOG_SERVER'),
					TEXT_HTTPS_SERVER_FORMAT_EXAMPLE
                ],
				'instructions' => [
					sprintf(TEXT_REMOVE_SUBDIRECTORY_FROM_END, 'HTTPS_CATALOG_SERVER'),
					sprintf(TEXT_SUBDIRECTORY_SETTINGS_INFO, 'HTTPS_CATALOG_SERVER')
                ]
            ];
		} elseif (preg_match('|/$|i', HTTPS_CATALOG_SERVER)) {
			$this->_config_files_error_messages[] = [
				'initial_desc' =>
					sprintf(TEXT_ERROR_VALUE_FOR_ADMIN_SERVER_VARIABLE, 'HTTPS_CATALOG_SERVER'),
				'current_value' => [
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'HTTPS_CATALOG_SERVER',
						HTTPS_CATALOG_SERVER),
                ],
				'extra_desc' => [
					sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_HAVE_A_SLASH_AT_END,
						'HTTPS_CATALOG_SERVER')
                ],
				'instructions' => [
					sprintf(TEXT_REMOVE_SLASH_FROM_END, 'HTTPS_CATALOG_SERVER')
                ]
            ];
		}


		if (!defined('DIR_WS_CATALOG') || strlen(DIR_WS_CATALOG) == 0) {
			$this->_config_files_error_messages[] = [
				'initial_desc' => sprintf(TEXT_ERROR_VALUE_NOT_SPECIFIED_FOR_ADMIN_SERVER_VARIABLE,
					'DIR_WS_CATALOG'),
				'extra_desc' => [
					TEXT_SERVER_VALUE_REQUIRED
                ],
				'instructions' => [
					sprintf(TEXT_SET_VALUE_TO_SLASH, 'DIR_WS_CATALOG'),
					sprintf(TEXT_SET_VALUE_TO_SUBDIRECTORY_NAME, 'DIR_WS_CATALOG')
                ]
            ];
		} elseif (DIR_WS_CATALOG != '/' && (!str_starts_with(DIR_WS_CATALOG, '/')
				|| !str_ends_with(DIR_WS_CATALOG, '/'))) {
			$error_message = [
				'initial_desc' =>
					sprintf(TEXT_ERROR_VALUE_FOR_ADMIN_SERVER_VARIABLE, 'DIR_WS_CATALOG'),
				'current_value' => [
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_CATALOG', DIR_WS_CATALOG),
                ]
            ];

			if (!str_starts_with(DIR_WS_CATALOG, '/') && !str_ends_with(DIR_WS_CATALOG, '/')) {
				$error_message['extra_desc'] = [
					sprintf(TEXT_VALUE_MUST_START_AND_END_WITH_SLASH, 'DIR_WS_CATALOG')
                ];

				$error_message['instructions'] = [
					sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_CATALOG'),
					sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_CATALOG')
                ];
			} elseif (!str_starts_with(DIR_WS_CATALOG, '/')) {
				$error_message['extra_desc'] = [
					sprintf(TEXT_VALUE_MUST_START_WITH_SLASH, 'DIR_WS_CATALOG')
                ];

				$error_message['instructions'] = [
					sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_CATALOG')
                ];
			} elseif (!str_ends_with(DIR_WS_CATALOG, '/')) {
				$error_message['extra_desc'] = [
					sprintf(TEXT_VALUE_MUST_END_WITH_SLASH, 'DIR_WS_CATALOG')
                ];

				$error_message['instructions'] = [
					sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_CATALOG')
                ];
			}

			$this->_config_files_error_messages[] = $error_message;
		}

		if (!defined('DIR_WS_HTTPS_CATALOG') || strlen(DIR_WS_HTTPS_CATALOG) == 0) {
			$this->_config_files_error_messages[] = [
				'initial_desc' => sprintf(TEXT_ERROR_VALUE_NOT_SPECIFIED_FOR_ADMIN_SERVER_VARIABLE,
					'DIR_WS_HTTPS_CATALOG'),
				'extra_desc' => [
					TEXT_SERVER_VALUE_REQUIRED
                ],
				'instructions' => [
					sprintf(TEXT_SET_VALUE_TO_SLASH, 'DIR_WS_HTTPS_CATALOG'),
					sprintf(TEXT_SET_VALUE_TO_SUBDIRECTORY_NAME, 'DIR_WS_HTTPS_CATALOG')
                ]
            ];
		} elseif (DIR_WS_HTTPS_CATALOG != '/' && (!str_starts_with(DIR_WS_HTTPS_CATALOG, '/')
				|| !str_ends_with(DIR_WS_HTTPS_CATALOG, '/'))) {
			$error_message = [
				'initial_desc' =>
					sprintf(TEXT_ERROR_VALUE_FOR_ADMIN_SERVER_VARIABLE, 'DIR_WS_HTTPS_CATALOG'),
				'current_value' => [
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_HTTPS_CATALOG',
						DIR_WS_HTTPS_CATALOG),
                ]
            ];

			if (!str_starts_with(DIR_WS_HTTPS_CATALOG, '/') && !str_ends_with(DIR_WS_HTTPS_CATALOG, '/')) {
				$error_message['extra_desc'] = [
					sprintf(TEXT_VALUE_MUST_START_AND_END_WITH_SLASH, 'DIR_WS_HTTPS_CATALOG')
                ];

				$error_message['instructions'] = [
					sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_HTTPS_CATALOG'),
					sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_HTTPS_CATALOG')
                ];
			} elseif (!str_starts_with(DIR_WS_HTTPS_CATALOG, '/')) {
				$error_message['extra_desc'] = [
					sprintf(TEXT_VALUE_MUST_START_WITH_SLASH, 'DIR_WS_HTTPS_CATALOG')
                ];

				$error_message['instructions'] = [
					sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_HTTPS_CATALOG')
                ];
			} elseif (!str_ends_with(DIR_WS_HTTPS_CATALOG, '/')) {
				$error_message['extra_desc'] = [
					sprintf(TEXT_VALUE_MUST_END_WITH_SLASH, 'DIR_WS_HTTPS_CATALOG')
                ];

				$error_message['instructions'] = [
					sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_HTTPS_CATALOG')
                ];
			}

			$this->_config_files_error_messages[] = $error_message;
		}

		if (DIR_WS_CATALOG != DIR_WS_HTTPS_CATALOG) {
			$this->_config_files_error_messages[] = [
				'initial_desc' => sprintf(TEXT_ERROR_ADMIN_HTTP_MUST_MATCH_HTTPS, 'DIR_WS_CATALOG',
					'DIR_WS_HTTPS_CATALOG'),
				'current_value' => [
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_CATALOG', DIR_WS_CATALOG),
					sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE,
						'DIR_WS_HTTPS_CATALOG', DIR_WS_HTTPS_CATALOG),
                ],
				'extra_desc' => [
					TEXT_WHEN_USING_STATIC_URIS_HTTP_MUST_MATCH_HTTPS,
					TEXT_SHARED_SSL_CANNOT_BE_USED
                ],
				'instructions' => [
					sprintf(TEXT_CHANGE_HTTP_TO_MATCH_HTTPS, 'DIR_WS_CATALOG',
						'DIR_WS_HTTPS_CATALOG'),
					TEXT_MAY_MEAN_PURCHASING_SSL_CERTIFICATE
                ]
            ];
		}

		// Now check the store's configure.php file

		// Must load in the text of the file so that its values can be parsed
		$store_config_file = file_get_contents(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'configure.php');

		if (!$store_config_file || strlen($store_config_file) < 10) {
			// Try the folder directly below the current folder
			$this->_config_files_error_messages[] = [
				'initial_desc' => TEXT_ERROR_UNABLE_TO_OPEN_STORE_CONFIGURE_FILE,
				'current_value' => [
					sprintf(TEXT_PATH_TO_STORE_CONFIGURE_FILE, DIR_FS_CATALOG . DIR_WS_INCLUDES . 'configure.php'),
                ],
				'instructions' => [
					TEXT_CHECK_PATH_TO_STORE_CONFIGURE_FILE
                ]
            ];
		} else {
			// Extract the values for the store's configure.php file
			$http_server = null;
			$https_server = null;
			$dir_ws_catalog = null;
			$dir_ws_https_catalog = null;

			if (!preg_match('|HTTP_SERVER[\'"]{1}[^,]*,[^\'"]*[\'"]{1}([^\'"]+)[\'"]{1}|', $store_config_file,
					$matches)) {
				// Couldn't find HTTP_SERVER value!
			} else {
				$http_server = $matches[1];
			}

			if (!preg_match('|HTTPS_SERVER[\'"]{1}[^,]*,[^\'"]*[\'"]{1}([^\'"]+)[\'"]{1}|', $store_config_file,
					$matches)) {
				// Couldn't find HTTPS_SERVER value!
			} else {
				$https_server = $matches[1];
			}

			if (!preg_match('|DIR_WS_CATALOG[\'"]{1}[^,]*,[^\'"]*[\'"]{1}([^\'"]*)[\'"]{1}|', $store_config_file,
					$matches)) {
				// Couldn't find DIR_WS_CATALOG value!
			} else {
				$dir_ws_catalog = $matches[1];
			}

			if (!preg_match('|DIR_WS_HTTPS_CATALOG[\'"]{1}[^,]*,[^\'"]*[\'"]{1}([^\'"]*)[\'"]{1}|',
					$store_config_file, $matches)) {
				// Couldn't find DIR_WS_HTTPS_CATALOG value!
			} else {
				$dir_ws_https_catalog = $matches[1];
			}

			if (preg_match('|^https?://[a-z0-9\-\.]+/.+$|i', $http_server)) {
				$this->_config_files_error_messages[] = [
					'initial_desc' =>
						sprintf(TEXT_ERROR_VALUE_FOR_SERVER_VARIABLE, 'HTTP_SERVER'),
					'current_value' => [
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'HTTP_SERVER', $http_server),
                    ],
					'extra_desc' => [
						sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_USE_SUBDIRECTORY, 'HTTP_SERVER'),
						sprintf(TEXT_HTTP_SERVER_VALUE_MUST_BE, 'HTTP_SERVER'),
						TEXT_HTTP_SERVER_FORMAT_EXAMPLE
                    ],
					'instructions' => [
						sprintf(TEXT_REMOVE_SUBDIRECTORY_FROM_END, 'HTTP_SERVER'),
						sprintf(TEXT_SUBDIRECTORY_SETTINGS_INFO, 'HTTP_SERVER')
                    ]
                ];
			} elseif (preg_match('|/$|i', $http_server)) {
				$this->_config_files_error_messages[] = [
					'initial_desc' =>
						sprintf(TEXT_ERROR_VALUE_FOR_SERVER_VARIABLE, 'HTTP_SERVER'),
					'current_value' => [
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'HTTP_SERVER', $http_server),
                    ],
					'extra_desc' => [
						sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_HAVE_A_SLASH_AT_END, 'HTTP_SERVER')
                    ],
					'instructions' => [
						sprintf(TEXT_REMOVE_SLASH_FROM_END, 'HTTP_SERVER')
                    ]
                ];
			}

			if (preg_match('|^https://[a-z0-9\-\.]+/.+$|i', $https_server)) {
				$this->_config_files_error_messages[] = [
					'initial_desc' =>
						sprintf(TEXT_ERROR_VALUE_FOR_SERVER_VARIABLE, 'HTTPS_SERVER'),
					'current_value' => [
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'HTTPS_SERVER', $https_server),
                    ],
					'extra_desc' => [
						sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_USE_SUBDIRECTORY, 'HTTPS_SERVER'),
						sprintf(TEXT_HTTPS_SERVER_VALUE_MUST_BE, 'HTTPS_SERVER'),
						TEXT_HTTPS_SERVER_FORMAT_EXAMPLE
                    ],
					'instructions' => [
						sprintf(TEXT_REMOVE_SUBDIRECTORY_FROM_END, 'HTTPS_SERVER'),
						sprintf(TEXT_SUBDIRECTORY_SETTINGS_INFO, 'HTTPS_SERVER')
                    ]
                ];
			} elseif (preg_match('|/$|i', $https_server)) {
				$this->_config_files_error_messages[] = [
					'initial_desc' =>
						sprintf(TEXT_ERROR_VALUE_FOR_SERVER_VARIABLE, 'HTTPS_SERVER'),
					'current_value' => [
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'HTTPS_SERVER', $https_server),
                    ],
					'extra_desc' => [
						sprintf(TEXT_HTTP_SERVER_VALUES_CANNOT_HAVE_A_SLASH_AT_END,
							'HTTPS_SERVER')
                    ],
					'instructions' => [
						sprintf(TEXT_REMOVE_SLASH_FROM_END, 'HTTPS_SERVER')
                    ]
                ];
			}


			if (strlen($dir_ws_catalog) == 0) {
				$this->_config_files_error_messages[] = [
					'initial_desc' => sprintf(TEXT_ERROR_VALUE_NOT_SPECIFIED_FOR_SERVER_VARIABLE,
						'DIR_WS_CATALOG'),
					'extra_desc' => [
						TEXT_SERVER_VALUE_REQUIRED
                    ],
					'instructions' => [
						sprintf(TEXT_SET_VALUE_TO_SLASH, 'DIR_WS_CATALOG'),
						sprintf(TEXT_SET_VALUE_TO_SUBDIRECTORY_NAME, 'DIR_WS_CATALOG')
                    ]
                ];
			} elseif ($dir_ws_catalog != '/' && (!str_starts_with($dir_ws_catalog, '/')
					|| !str_ends_with($dir_ws_catalog, '/'))) {
				$error_message = [
					'initial_desc' =>
						sprintf(TEXT_ERROR_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_CATALOG'),
					'current_value' => [
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_CATALOG',
							$dir_ws_catalog),
                    ]
                ];

				if (!str_starts_with($dir_ws_catalog, '/') && !str_ends_with($dir_ws_catalog, '/')) {
					$error_message['extra_desc'] = [
						sprintf(TEXT_VALUE_MUST_START_AND_END_WITH_SLASH, 'DIR_WS_CATALOG')
                    ];

					$error_message['instructions'] = [
						sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_CATALOG'),
						sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_CATALOG')
                    ];
				} elseif (!str_starts_with($dir_ws_catalog, '/')) {
					$error_message['extra_desc'] = [
						sprintf(TEXT_VALUE_MUST_START_WITH_SLASH, 'DIR_WS_CATALOG')
                    ];

					$error_message['instructions'] = [
						sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_CATALOG')
                    ];
				} elseif (!str_ends_with($dir_ws_catalog, '/')) {
					$error_message['extra_desc'] = [
						sprintf(TEXT_VALUE_MUST_END_WITH_SLASH, 'DIR_WS_CATALOG')
                    ];

					$error_message['instructions'] = [
						sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_CATALOG')
                    ];
				}

				$this->_config_files_error_messages[] = $error_message;
			}

			if (strlen($dir_ws_https_catalog) == 0) {
				$this->_config_files_error_messages[] = [
					'initial_desc' => sprintf(TEXT_ERROR_VALUE_NOT_SPECIFIED_FOR_SERVER_VARIABLE,
						'DIR_WS_HTTPS_CATALOG'),
					'extra_desc' => [
						TEXT_SERVER_VALUE_REQUIRED
                    ],
					'instructions' => [
						sprintf(TEXT_SET_VALUE_TO_SLASH, 'DIR_WS_HTTPS_CATALOG'),
						sprintf(TEXT_SET_VALUE_TO_SUBDIRECTORY_NAME, 'DIR_WS_HTTPS_CATALOG')
                    ]
                ];
			} elseif ($dir_ws_https_catalog != '/' && (!str_starts_with($dir_ws_https_catalog, '/')
					|| !str_ends_with($dir_ws_https_catalog, '/'))) {
				$error_message = [
					'initial_desc' =>
						sprintf(TEXT_ERROR_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_HTTPS_CATALOG'),
					'current_value' => [
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_HTTPS_CATALOG',
							$dir_ws_https_catalog),
                    ]
                ];

				if (!str_starts_with($dir_ws_https_catalog, '/') && !str_ends_with($dir_ws_https_catalog, '/')) {
					$error_message['extra_desc'] = [
						sprintf(TEXT_VALUE_MUST_START_AND_END_WITH_SLASH, 'DIR_WS_HTTPS_CATALOG')
                    ];

					$error_message['instructions'] = [
						sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_HTTPS_CATALOG'),
						sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_HTTPS_CATALOG')
                    ];
				} elseif (!str_starts_with($dir_ws_https_catalog, '/')) {
					$error_message['extra_desc'] = [
						sprintf(TEXT_VALUE_MUST_START_WITH_SLASH, 'DIR_WS_HTTPS_CATALOG')
                    ];

					$error_message['instructions'] = [
						sprintf(TEXT_ADD_A_STARTING_SLASH, 'DIR_WS_HTTPS_CATALOG')
                    ];
				} elseif (!str_ends_with($dir_ws_https_catalog, '/')) {
					$error_message['extra_desc'] = [
						sprintf(TEXT_VALUE_MUST_END_WITH_SLASH, 'DIR_WS_HTTPS_CATALOG')
                    ];

					$error_message['instructions'] = [
						sprintf(TEXT_ADD_AN_ENDING_SLASH, 'DIR_WS_HTTPS_CATALOG')
                    ];
				}

				$this->_config_files_error_messages[] = $error_message;
			}

			if ($dir_ws_catalog != $dir_ws_https_catalog) {
				$this->_config_files_error_messages[] = [
					'initial_desc' => sprintf(TEXT_ERROR_HTTP_MUST_MATCH_HTTPS, 'DIR_WS_CATALOG',
						'DIR_WS_HTTPS_CATALOG'),
					'current_value' => [
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE, 'DIR_WS_CATALOG',
							$dir_ws_catalog),
						sprintf(TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE,
							'DIR_WS_HTTPS_CATALOG', $dir_ws_https_catalog),
                    ],
					'extra_desc' => [
						TEXT_WHEN_USING_STATIC_URIS_HTTP_MUST_MATCH_HTTPS,
						TEXT_SHARED_SSL_CANNOT_BE_USED
                    ],
					'instructions' => [
						sprintf(TEXT_CHANGE_HTTP_TO_MATCH_HTTPS, 'DIR_WS_CATALOG',
							'DIR_WS_HTTPS_CATALOG'),
						TEXT_MAY_MEAN_PURCHASING_SSL_CERTIFICATE
                    ]
                ];
			}

			// Make sure admin configure file values match store side's values

		}
	}

	// }}}


	// {{{ _checkModifiedCoreFiles()

	/**
	 * Examines the store's files to check if any of the Ceon URI Mapping core file modifications are missing, or
	 * if any old modifications haven't been updated to the new ones. Adds the appropriate error messages to the
	 * main error messages property.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _checkModifiedCoreFiles(): void
    {
		// Main variable holds the list of files to check and information about the modifications expected to be
		// found in each, in order for Ceon URI Mapping's functionality to have been correctly installed
		$core_file_modifications = [];

		$core_file_modifications[] = [
			'path' => DIR_WS_INCLUDES . 'javascript_loader.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'|1 of 1 --\>[\s]+\<\?php if \(file_exists\(DIR_WS_INCLUDES \. \'ceon_uri_mapping_javascript\.php\'\)\) require DIR_WS_INCLUDES \. \'ceon_uri_mapping_javascript\.php\'; \?\>|',
/*					'require DIR_WS_INCLUDES \. \'ceon_uri_mapping_javascript.php\';'*/
                ],
            ],
			'min_version_add' => [
				'1.5.6'
            ],
			'min_version_rem' => [
				'1.5.7'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_FUNCTIONS . 'general.php',
			'num_mods' => 2,
			'new_snippets' => [
				[
					'|1 of 2[\s]+global \$ceon_uri_mapping_admin;|',
					'$ceon_uri_mapping_admin->deleteURIMappings($selections);'
                ],
				[
					'|2 of 2[\s]+global \$ceon_uri_mapping_admin, \$ceon_uri_mapping_product_pages,|',
					'$ceon_uri_mapping_admin->deleteURIMappings($selections);'
                ]
            ],
			'old_snippets' => [
				'MAPPING |2 of 2[\s]+global $ceon_uri_mapping_admin;',
				'|main_page = \'" \. FILENAME_DEFAULT \. "\'[\s]+AND[\s]+associated_db_id = \'" \. \(int\) \$category_id|',
				'|main_page = \'" \. zen_db_input\(FILENAME_TELL_A_FRIEND\) \. "\'[\s]+\)[\s]+AND[\s]+associated_db_id = \'" \. \(int\) \$product_id|'
            ],
			'min_version_rem' => [
				'1.5.5'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_FUNCTIONS . 'html_output.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'|1 of 1[\s]+if \(defined\(\'CEON_URI_MAPPING_ENABLED\'\)|',
					'|return \$ceon_uri_mapping_href_link_builder\->getHREFLink\(\);|'
                ]
            ],
			'min_version_rem' => [
				'1.5.5'
            ],
        ];

		// As the product types share their modifications, a basic template is defined here to save wasted coding
		$product_type_collect_info_modifications = [
			'num_mods' => 2,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->collectInfoHandler();'
                ],
				[
					'echo $ceon_uri_mapping_admin->collectInfoBuildURIMappingFields();'
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = false;',
				'echo ceon_uri_mapping_build_product_uri_fields($prev_uri_mappings, $uri_mappings,'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$product_type_preview_info_modifications = [
			'num_mods' => 4,
			'new_snippets' => [
				[
					'|1 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminProd|',
					'$ceon_uri_mapping_admin->productPreviewProcessSubmission($current_category_id)'
                ],
				[
					'|2 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminProd|',
					'$ceon_uri_mapping_admin->productPreviewInitialLoad((int) $_GET[\'pID\'],'
                ],
				[
					'$ceon_uri_mapping_admin->productPreviewOutputURIMappingInfo($languages[$i]);'
                ],
				[
					'echo $ceon_uri_mapping_admin->productPreviewBuildHiddenFields();'
                ]
            ],
			'old_snippets' => [
				'$prev_uri_mappings = $_POST[\'prev_uri_mappings\'];',
				'$prev_uri_mappings = array();',
				'if (!is_null($uri_mappings[$languages[$i][\'id\']]) &&',
				'echo zen_draw_hidden_field(\'prev_uri_mappings'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$product_types = [
			'document_general',
			'document_product',
			'product',
			'product_book',
			'product_free_shipping',
			'product_music'
        ];

		foreach ($product_types as $product_type) {
			// Add the collect info modifications information for this product type to the main array
			$current_product_collect_info_modifications = $product_type_collect_info_modifications;

			$current_product_collect_info_modifications['path'] =
				DIR_WS_MODULES . $product_type . '/' . 'collect_info.php';

			$core_file_modifications[] = $current_product_collect_info_modifications;

			// Add the preview info modifications information for this product type to the main array
			$current_product_preview_info_modifications = $product_type_preview_info_modifications;

			$current_product_preview_info_modifications['path'] =
				DIR_WS_MODULES . $product_type . '/' . 'preview_info.php';

			$core_file_modifications[] = $current_product_preview_info_modifications;
		}

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'copy_to_confirm.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->copyToConfirmHandler($products_id_from,'
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = (($_POST[\'uri_mapping\'] == \'autogen\')'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'delete_product_confirm.php',
			'num_mods' => 0,
			'num_prev_mods' => 1,
			'old_snippets' => [
				'$db->Execute("DELETE FROM " . TABLE_CEON_URI_MAPPINGS'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'move_product_confirm.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->moveProductConfirmHandler($products_id, $product_type'
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = (($_POST[\'uri_mapping\'] == \'autogen\')'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'sidebox_move_product.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToProductMoveFieldsArray('
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_input_fields = ceon_uri_mapping_build_product_move_uri_fields('
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'update_product.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'updateProductHandler($products_id, $zc_products->get_handler($product_type));'
                ]
            ],
			'old_snippets' => [
				'$prev_uri_mapping = trim($_POST[\'prev_uri_mappings'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'product_book/copy_to_confirm.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->copyToConfirmHandler($products_id_from,'
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = (($_POST[\'uri_mapping\'] == \'autogen\')'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'product_book/delete_product_confirm.php',
			'num_mods' => 0,
			'num_prev_mods' => 1,
			'old_snippets' => [
				'$db->Execute("DELETE FROM " . TABLE_CEON_URI_MAPPINGS'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'product_book/update_product.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'updateProductHandler($products_id, $zc_products->get_handler($product_type));'
                ]
            ],
			'old_snippets' => [
				'$prev_uri_mapping = trim($_POST[\'prev_uri_mappings'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'product_music/copy_to_confirm.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->copyToConfirmHandler($products_id_from,'
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = (($_POST[\'uri_mapping\'] == \'autogen\')'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'product_music/delete_product_confirm.php',
			'num_mods' => 0,
			'num_prev_mods' => 1,
			'old_snippets' => [
				'$db->Execute("DELETE FROM " . TABLE_CEON_URI_MAPPINGS'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_WS_MODULES . 'product_music/update_product.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'updateProductHandler($products_id, $zc_products->get_handler($product_type));'
                ]
            ],
			'old_snippets' => [
				'$prev_uri_mapping = trim($_POST[\'prev_uri_mappings'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'categories.php',
			'num_mods' => 3,
			'new_snippets' => [
				[
					'|1 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->insertUpdateHandler($categories_id, $current_category'
                ],
				[
					'|2 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryFieldsArray();'
                ],
				[
					'|3 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray('
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = (isset($_POST[\'uri_mapping_autogen',
				'$db->Execute("DELETE FROM " . TABLE_CEON_URI_MAPPINGS . "',
				'$db->Execute("DELETE FROM " . TABLE_CEON_URI_MAPPINGS . "',
				'// New category doesn\'t have any previous URI mappings',
				'$prev_uri_mappings_sql = "'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'document_general.php',
			'num_mods' => 3,
			'new_snippets' => [
				[
					'|1 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryFieldsArray();'
                ],
				[
					'|2 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray('
                ],
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToProductCopyFieldsArray((int) $_G'
                ]
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'document_product.php',
			'num_mods' => 3,
			'new_snippets' => [
				[
					'|1 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryFieldsArray();'
                ],
				[
					'|2 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray('
                ],
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToProductCopyFieldsArray((int) $_G'
                ]
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'ezpages.php',
			'num_mods' => 4,
			'new_snippets' => [
				[
					'|1 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminEZPa|',
					'$ceon_uri_mapping_admin->insertUpdateHandler($pages_id,'
                ],
				[
					'|2 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminEZPa|',
					'$ceon_uri_mapping_admin->deleteConfirmHandler($pages_id);'
                ],
				[
					'|3 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminEZPa|',
					'$ceon_uri_mapping_admin->configureEnvironment();'
                ],
				[
					'echo $ceon_uri_mapping_admin->buildEZPageURIMappingFields();'
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = (isset($_POST[\'uri_mapping_autogen',
				'$db->Execute("DELETE FROM " . TABLE_CEON_URI_MAPPINGS . "',
				'$uri_mapping_autogen = false;',
				'echo ceon_uri_mapping_build_ez_page_uri_fields($prev_uri_mappings, $uri_mappings,'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'manufacturers.php',
			'num_mods' => 4,
			'new_snippets' => [
				[
					'|1 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminManu|',
					'$ceon_uri_mapping_admin->insertSaveHandler((int) $manufacturers_id, $manufactu'
                ],
				[
					'|2 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminManu|',
					'$ceon_uri_mapping_admin->deleteConfirmHandler((int) $manufacturers_id);'
                ],
				[
					'|3 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminManu|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToAddManufacturerFieldsArray();'
                ],
				[
					'|4 of 4[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminManu|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToEditManufacturerFieldsArray('
                ]
            ],
			'old_snippets' => [
				'$uri_mapping_autogen = (isset($_POST[\'uri_mapping_autogen',
				'$db->Execute("DELETE FROM " . TABLE_CEON_URI_MAPPINGS . "',
				'// New manufacturer doesn\'t have any previous URI mappings',
				'$prev_uri_mappings_sql = "'
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'product.php',
			'num_mods' => 3,
			'new_snippets' => [
				[
					'|1 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryFieldsArray();'
                ],
				[
					'|2 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray('
                ],
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToProductCopyFieldsArray((int) $_G'
                ]
            ],
			'old_snippets' => [
				'// New category doesn\'t have any previous URI mappings',
				'$prev_uri_mappings_sql = "',
				'$uri_mapping_input_fields = ceon_uri_mapping_build_product_copy_uri_fields('
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'product_music.php',
			'num_mods' => 3,
			'new_snippets' => [
				[
					'|1 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryFieldsArray();'
                ],
				[
					'|2 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray('
                ],
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToProductCopyFieldsArray((int) $_G'
                ]
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => 'product_free_shipping.php',
			'num_mods' => 3,
			'new_snippets' => [
				[
					'|1 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryFieldsArray();'
                ],
				[
					'|2 of 3[\s]+require_once\(DIR_WS_CLASSES \. \'class\.CeonURIMappingAdminCate|',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray('
                ],
				[
					'require_once(DIR_WS_CLASSES . \'class.CeonURIMappingAdminProductPages.php\');',
					'$ceon_uri_mapping_admin->addURIMappingFieldsToProductCopyFieldsArray((int) $_G'
                ]
            ],
			'min_version_rem' => [
				'1.5.6'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php',
			'num_mods' => 1,
			'new_snippets' => [
				[
					'if (!isset($ceon_uri_mapping_href_link_builder)) {',
					'return $ceon_uri_mapping_href_link_builder->getHREFLink();'
                ]
            ],
			'old_snippets' => [
				'|1 of [3-4]+[\s]+global \$db|',
				'if (defined(\'CEON_URI_MAPPING_ENABLED\') && CEON_URI_MAPPING_ENABLED == 1) {',
				'|3 of 4[\s]+}|',
				'if (strpos($link, \'?\') === false) {'
            ],
			'min_version_rem' => [
				'1.5.5'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_FS_CATALOG . DIR_WS_INCLUDES . 'init_includes/overrides/init_canonical.php',
			'num_mods' => 2,
			'file_required' => true,
			'new_snippets' => [
				[
					'if (isset($ceon_uri_mapping_canonical_uri)) {'
                ],
				[
					'|2 of 2[\s]+\}|'
                ]
            ],
			'old_snippets' => [
				'if (!isset($ceon_uri_mapping_canonical_uri)) {'
            ],
			'min_version_rem' => [
				'1.5.5'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_FS_CATALOG . DIR_WS_INCLUDES . 'init_includes/overrides/init_currencies.php',
			'num_mods' => 1,
			'file_required' => true,
			'new_snippets' => [
				[
					'if (isset($ceon_uri_mapping)) {'
                ]
            ],
			'min_version_rem' => [
				'1.3.8'
            ],
        ];

		$core_file_modifications[] = [
			'path' => DIR_FS_CATALOG . DIR_WS_INCLUDES . 'extra_datafiles/ceon_uri_mapping_sessions_define.php',
			'num_mods' => 0,
			'old_snippets' => [
				'define(\'CUSTOM_COOKIE_PATH\', \'/\')',
            ],
        ];

		// Next two checks are for specific Zen Cart versions
		$zen_cart_minor_version = (float) substr(PROJECT_VERSION_MINOR, 0, 3);

		// Sessions used root until 1.3.9
		if (PROJECT_VERSION_MAJOR == '1' && $zen_cart_minor_version >= 3.9) {
			$core_file_modifications[] = [
				'path' => DIR_FS_CATALOG . DIR_WS_INCLUDES .
					'init_includes/overrides/init_sessions.php',
				'num_mods' => 1,
				'file_required' => true,
				'new_snippets' => [
					[
						'|for the root[\s]+\$path = \'/\';|'
                    ]
                ],
				'min_version_rem' => [
					'1.5.0'
                ],
            ];
		}

		// html_header.php is only modified for old Zen Cart versions
		if (PROJECT_VERSION_MAJOR == '1' && $zen_cart_minor_version < 3.9) {
			global $template_dir;

			$core_file_modifications[] = [
				'path' => DIR_FS_CATALOG_TEMPLATES . $template_dir . '/common/html_header.php',
				'file_required' => true,
				'num_mods' => 1,
				'new_snippets' => [
					[
						'if (isset($canonicalLink) && $canonicalLink !='
                    ]
                ]
            ];
		}

		// Now that the list of core file modifications info has been built, analyse the store's files to see if
		// there are any missing/wrong
		// @TODO This doesn't take into account snippets which have the correct start but the wrong end and a
		// following snippet has the same start. Both the start and end must match for the snippet to be considered
		// matched, and the scan to be moved on
		foreach ($core_file_modifications as $core_file_modification) {
			$file_source = @file_get_contents($core_file_modification['path']);

			// Ignore files that cannot be found/opened unless specifically told not to
			if (!$file_source && isset($core_file_modification['file_required']) &&
					$core_file_modification['file_required'] &&
					(isset($core_file_modification['min_version_rem'][0]) ?
					(PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version) < $core_file_modification['min_version_rem'][0] :
					(!isset($core_file_modification['min_version_add'][0]) || (PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version) >= $core_file_modification['min_version_add'][0]))) {
				// Let user know that this file must be uploaded and modified
				if ($core_file_modification['num_mods'] == 1) {
					$this->_modified_core_files_error_messages[] = [
						'initial_desc' => TEXT_ERROR_FILE_MUST_BE_MODIFIED_BUT_DOES_NOT_EXIST,
						'current_value' => [
							sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, $core_file_modification['path'])
                        ],
						'instructions' => [
							TEXT_ADD_MISSING_FILE_WITH_MODIFICATIONS
                        ]
                    ];
				} else {
					$this->_modified_core_files_error_messages[] = [
						'initial_desc' =>
							TEXT_ERROR_FILE_MUST_HAVE_MULTIPLE_MODS_BUT_DOES_NOT_EXIST,
						'current_value' => [
							sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, $core_file_modification['path'])
                        ],
						'instructions' => [
							TEXT_ADD_MISSING_FILE_WITH_MODIFICATIONS
                        ]
                    ];
				}

				continue;

			} elseif (!$file_source &&
				(isset($core_file_modification['min_version_rem'][0]) ?
				(PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version) < $core_file_modification['min_version_rem'][0] :
				(!isset($core_file_modification['min_version_add'][0]) || (PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version) >= $core_file_modification['min_version_add'][0]))) {
				continue;
			}

			// Check how many of the new modifications are present in the file /////////////////////
			$num_new_code_snippets_present = 0;

			$new_code_snippets_present = [];

			$new_code_snippets_missing = [];

			$old_code_snippets_present = [];

			// Check how many of the old modifications are present in the file
			$num_previous_code_snippets_remaining = 0;

			$offset = 0;

			// Need to add check against removed files based on the version.
			for ($i = 0, $n = (isset($core_file_modification['new_snippets']) ? sizeof($core_file_modification['new_snippets']) : 0); $i < $n; $i++) {
				$snippet_start_line = $core_file_modification['new_snippets'][$i][0];

				$snippet_start_line_pos = strpos($file_source, $snippet_start_line, $offset);

				// Is this a regular expression or a simple string comparison?
				if (str_starts_with($snippet_start_line, '|')) {
					$snippet_start_line_pos = false;

					// Regular expression
					if (preg_match($snippet_start_line, $file_source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
						$snippet_start_line_pos = $matches[0][1];

						// The start line is not the regular expression but the string it matched
						$snippet_start_line =  $matches[0][0];
					}
				}

				$snippet_end_line = null;
				$snippet_end_line_pos = false;

				// Move the scan on to after this snippet
				if ($snippet_start_line_pos !== false) {
					$offset_for_end_search = $snippet_start_line_pos + strlen($snippet_start_line);

					if (isset($core_file_modification['new_snippets'][$i][1])) {
						$snippet_end_line = $core_file_modification['new_snippets'][$i][1];

						$snippet_end_line_pos =
							strpos($file_source, $snippet_end_line, $offset_for_end_search);

						// Is this a regular expression or a simple string comparison?
						if (str_starts_with($snippet_end_line, '|')) {
							$snippet_end_line_pos = false;

							// Regular expression
							if (preg_match($snippet_end_line, $file_source, $matches, PREG_OFFSET_CAPTURE,
									$offset_for_end_search)) {
								$snippet_end_line_pos = $matches[0][1];

								// The end line is not the regular expression but the string it
								// matched
								$snippet_end_line =  $matches[0][0];
							}
						}
					}
				}

				if ($snippet_start_line_pos !== false &&
						(is_null($snippet_end_line) || $snippet_end_line_pos !== false)) {
					// Found a snippet.
					if (isset($core_file_modification['min_version_rem'][0]) ?
						PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version < $core_file_modification['min_version_rem'][0] :
						(!isset($core_file_modification['min_version_add'][0]) || (PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version) >= $core_file_modification['min_version_add'][0])) {
						$new_code_snippets_present[] = $i + 1;

						$num_new_code_snippets_present++;
					} else {
						$core_file_modification['num_mods']--;
						$num_previous_code_snippets_remaining++;
						$old_code_snippets_present[] = $i + 1;
					}

					$offset = $snippet_end_line_pos + (isset($snippet_end_line) ? strlen($snippet_end_line) : 0);

					if (is_null($snippet_end_line)) {
						$offset = $snippet_start_line_pos + strlen($snippet_start_line);
					}
				} else {
					// new required snippet is missing.
					if (isset($core_file_modification['min_version_rem'][0]) ?
							PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version < $core_file_modification['min_version_rem'][0] :
							(!isset($core_file_modification['min_version_add'][0]) || (PROJECT_VERSION_MAJOR . '.' . $zen_cart_minor_version) >= $core_file_modification['min_version_add'][0])) {
						$new_code_snippets_missing[] = $i + 1;
					} else {
						$core_file_modification['num_mods']--;
						$old_code_snippets_present[] = $i + 1;
					}
				}
			}


			$old_code_snippets_remaining = [];

			$offset = 0;

			for ($i = 0, $n = (isset($core_file_modification['old_snippets']) ? count($core_file_modification['old_snippets']) : 0); $i < $n; $i++) {
				$snippet_start_line = $core_file_modification['old_snippets'][$i];
				$snippet_start_line_pos = strpos($file_source, $snippet_start_line, $offset);

				// Is this a regular expression or a simple string comparison?
				if (str_starts_with($snippet_start_line, '|')) {
					$snippet_start_line_pos = false;

					// Regular expression
					if (preg_match($snippet_start_line, $file_source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
						$snippet_start_line_pos = $matches[0][1];

						// The start line is not the regular expression but the string it matched
						$snippet_start_line =  $matches[0][0];
					}
				}

				if ($snippet_start_line_pos !== false) {
					$num_previous_code_snippets_remaining++;

					// Move the scan on to after this snippet
					$offset = $snippet_start_line_pos + strlen($snippet_start_line);

					$old_code_snippets_remaining[] = $i + 1;
				}
			}

			// Check how many code comment lines are present in the modified core file /////////////
			$num_mod_start_lines_in_file =
				preg_match_all('|// BEGIN CEON URI MAPPING ([0-9]{1,2}) of [0-9]{1,2}|',
				$file_source, $mod_start_matches, PREG_SET_ORDER);

			$num_mod_end_lines_in_file = preg_match_all('|// END CEON URI MAPPING ([0-9]{1,2}) of [0-9]{1,2}|',
				$file_source, $mod_end_matches, PREG_SET_ORDER);

			$num_mod_lines = $num_mod_start_lines_in_file + $num_mod_end_lines_in_file;

			$mod_start_lines_with_missing_end = [];
			$mod_end_lines_with_missing_start = [];

			foreach ($mod_start_matches as $mod_start_line_info) {
				foreach ($mod_end_matches as $mod_end_line_info) {
					if ($mod_start_line_info[1] == $mod_end_line_info[1]) {
						continue 2;
					}
				}

				// Reaching this point means no match was found for the current start line
				$mod_start_lines_with_missing_end[] = $mod_start_line_info[1];
			}

			foreach ($mod_end_matches as $mod_end_line_info) {
				foreach ($mod_start_matches as $mod_start_line_info) {
					if ($mod_end_line_info[1] == $mod_start_line_info[1]) {
						continue 2;
					}
				}

				// Reaching this point means no match was found for the current end line
				$mod_end_lines_with_missing_start[] = $mod_end_line_info[1];
			}


			// Analyse the data and warn the user about any mistakes they've made modifying the file
			if ($core_file_modification['num_mods'] == 0 && ($num_mod_lines > 0 ||
					$num_previous_code_snippets_remaining > 0)) {
				// This file no longer has modifications but comments have been found in it
				if ($num_previous_code_snippets_remaining == 1) {
					// Add an error message for a file which no longer has snippets but has one old one remaining
					$this->_modified_core_files_error_messages[] = [
						'initial_desc' => TEXT_ERROR_MODS_NO_LONGER_ONE_OLD_REMAINS,
						'current_value' => [
							sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                        ],
						'extra_desc' => [
							TEXT_FILE_MODIFICATION_NO_LONGER_REQUIRED
                        ],
						'instructions' => [
							TEXT_REMOVE_THE_MODIFICATION,
							TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REMOVE_MODIFICATIONS
                        ]
                    ];
				} elseif ($num_previous_code_snippets_remaining > 1) {
					// Add an error message for a file which no longer has snippets but has x old ones remaining
					$this->_modified_core_files_error_messages[] = [
						'initial_desc' => sprintf(TEXT_ERROR_MODS_NO_LONGER_X_REMAIN,
							$num_previous_code_snippets_remaining),
						'current_value' => [
							sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                        ],
						'extra_desc' => [
							TEXT_FILE_MODIFICATION_NO_LONGER_REQUIRED
                        ],
						'instructions' => [
							TEXT_REMOVE_THE_MODIFICATIONS,
							TEXT_REMOVE_THE_MODIFICATIONS_2,
							TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REMOVE_MODIFICATIONS
                        ]
                    ];
				} elseif ($num_mod_lines == 1) {
					// Add an error message for a file which no longer has snippets but has 1 comment remaining
					$this->_modified_core_files_error_messages[] = [
						'initial_desc' => TEXT_ERROR_MODS_NO_LONGER_COMMENT_REMAINS,
						'current_value' => [
							sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                        ],
						'extra_desc' => [
							TEXT_MARKER_COMMENT_FOUND,
							TEXT_MARKER_COMMENTS_FOUND_2
                        ],
						'instructions' => [
							TEXT_CHECK_MODIFICATIONS_REMOVED_PROPERLY_1_COMMENT,
							TEXT_MARKER_COMMENTS_INFO,
							TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REMOVE_MODIFICATIONS
                        ]
                    ];
				} else {
					// Add an error message for a file which no longer has snippets but has x comments remaining
					$this->_modified_core_files_error_messages[] = [
						'initial_desc' => sprintf(TEXT_ERROR_MODS_NO_LONGER_COMMENTS_REMAIN, $num_mod_lines),
						'current_value' => [
							sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                        ],
						'extra_desc' => [
							sprintf(TEXT_MARKER_COMMENTS_FOUND, $num_mod_lines),
							TEXT_MARKER_COMMENTS_FOUND_2
                        ],
						'instructions' => [
							TEXT_CHECK_MODIFICATIONS_REMOVED_PROPERLY_X_COMMENTS,
							TEXT_MARKER_COMMENTS_INFO,
							TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REMOVE_MODIFICATIONS
                        ]
                    ];
				}
			} elseif ($num_new_code_snippets_present != $core_file_modification['num_mods']) {
				if ($num_new_code_snippets_present == 0) {
					// None of the new code snippets are present
					if ($num_previous_code_snippets_remaining > 0) {
						// At least one old snippet remains
						if ($core_file_modification['num_mods'] == $num_previous_code_snippets_remaining) {
							// Assuming user forgot to update the file
							if ($core_file_modification['num_mods'] == 1) {
								// Add an error message for a file with its single snippet still using old code
								$this->_modified_core_files_error_messages[] = [
									'initial_desc' => TEXT_ERROR_OLD_MOD_PRESENT,
									'current_value' => [
										sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
											realpath($core_file_modification['path']))
                                    ],
									'extra_desc' => [
										TEXT_FILE_MODIFICATION_NEEDS_UPDATING
                                    ],
									'instructions' => [
										TEXT_UPDATE_THE_MODIFICATION,
										TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                    ]
                                ];
							} else {
								// Add an error message for a file with all snippets still using old code
								$this->_modified_core_files_error_messages[] = [
									'initial_desc' => sprintf(TEXT_ERROR_X_OLD_MODS_PRESENT,
										$core_file_modification['num_mods']),
									'current_value' => [
										sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
											realpath($core_file_modification['path']))
                                    ],
									'extra_desc' => [
										TEXT_FILE_MODIFICATIONS_NEED_UPDATING
                                    ],
									'instructions' => [
										sprintf(TEXT_UPDATE_THE_MODIFICATIONS,
											$num_previous_code_snippets_remaining,
											$num_previous_code_snippets_remaining),
										TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                    ]
                                ];
							}
						} elseif ($core_file_modification['num_mods'] == 1) {
							// Add an error message for a file with its single snippet missing but x old snippets
							// remaining
							$this->_modified_core_files_error_messages[] = [
								'initial_desc' => TEXT_ERROR_MOD_MISSING_OLD_MODS_PRESENT,
								'current_value' => [
									sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
										realpath($core_file_modification['path']))
                                ],
								'extra_desc' => [
									TEXT_FILE_MODIFICATION_NEEDS_APPLYING_OLD_REMOVING
                                ],
								'instructions' => [
									sprintf(TEXT_REMOVE_X_OLD_MODS_APPLY_NEW,
										$num_previous_code_snippets_remaining),
									TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                ]
                            ];
						} elseif ($num_previous_code_snippets_remaining == 1) {
							// Add an error message for a file with its x snippets missing but an old snippet remaining
							$this->_modified_core_files_error_messages[] = [
								'initial_desc' =>
									sprintf(TEXT_ERROR_ALL_X_MODS_MISSING_OLD_MOD_REMAINS,
										$core_file_modification['num_mods']),
								'current_value' => [
									sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
										realpath($core_file_modification['path']))
                                ],
								'extra_desc' => [
									TEXT_FILE_HAS_BEEN_MODIFIED_NEW_MODS_NEED_APPLYING
                                ],
								'instructions' => [
									sprintf(TEXT_REMOVE_OLD_MOD_APPLY_X_NEW, $core_file_modification['num_mods']),
									TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                ]
                            ];
						} else {
							// Add an error message for a file with its x snippets missing but x old snippets remaining
							$this->_modified_core_files_error_messages[] = [
								'initial_desc' =>
									sprintf(TEXT_ERROR_ALL_X_MODS_MISSING_X_OLD_MODS_REMAIN,
										$core_file_modification['num_mods'],
										$num_previous_code_snippets_remaining),
								'current_value' => [
									sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
										realpath($core_file_modification['path']))
                                ],
								'extra_desc' => [
									TEXT_FILE_HAS_BEEN_MODIFIED_NEW_MODS_NEED_APPLYING
                                ],
								'instructions' => [
									sprintf(TEXT_REMOVE_X_OLD_MODS_APPLY_X_NEW,
										$num_previous_code_snippets_remaining,
										$core_file_modification['num_mods']),
									TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                ]
                            ];
						}
					} elseif ($num_mod_lines > 0) {
						// Although no modifications have been found, comment(s) are in the file
						if ($core_file_modification['num_mods'] == 1) {
							// Add an error message for a file with its single snippet missing but x comments found
							$this->_modified_core_files_error_messages[] = [
								'initial_desc' => sprintf(TEXT_ERROR_MOD_MISSING_X_COMMENTS_PRESENT,
									$num_mod_lines),
								'current_value' => [
									sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
										realpath($core_file_modification['path']))
                                ],
								'extra_desc' => [
									TEXT_FILE_HAS_BEEN_MODIFIED_SOMEHOW_MOD_MISSING
                                ],
								'instructions' => [
									TEXT_CHECK_FILE_APPLY_MOD,
									TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                ]
                            ];
						} else {
							// Add an error message for a core file not having been modified but comment(s) found
							$this->_modified_core_files_error_messages[] = [
								'initial_desc' => sprintf(TEXT_ERROR_ALL_X_MODS_MISSING_X_COMMENTS_PRESENT,
									$core_file_modification['num_mods'], $num_mod_lines),
								'current_value' => [
									sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
										realpath($core_file_modification['path']))
                                ],
								'extra_desc' => [
									TEXT_FILE_HAS_BEEN_MODIFIED_SOMEHOW_MODS_MISSING
                                ],
								'instructions' => [
									sprintf(TEXT_CHECK_FILE_APPLY_MODS, $core_file_modification['num_mods']),
									TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                ]
                            ];
						}
					} else {
						// There have been no core file modifications for this file at all
						if ($core_file_modification['num_mods'] == 1) {
							// Add an error message for a core file not having been modified, with snippet not having
							// been added
							$this->_modified_core_files_error_messages[] = [
								'initial_desc' => TEXT_ERROR_MOD_MISSING,
								'current_value' => [
									sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
										realpath($core_file_modification['path']))
                                ],
								'instructions' => [
									TEXT_APPLY_MOD,
									TEXT_ALTERNATIVELY_REPLACE_FILE_TO_APPLY_MODIFICATIONS
                                ]
                            ];
						} else {
							// Add an error message for a core file not having been modified, with x
							// snippets not having been added
							$this->_modified_core_files_error_messages[] = [
								'initial_desc' => sprintf(TEXT_ERROR_X_MODS_MISSING,
									$core_file_modification['num_mods']),
								'current_value' => [
									sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
										realpath($core_file_modification['path']))
                                ],
								'instructions' => [
									sprintf(TEXT_APPLY_X_MODS, $core_file_modification['num_mods']),
									TEXT_ALTERNATIVELY_REPLACE_FILE_TO_APPLY_MODIFICATIONS
                                ]
                            ];
						}
					}
				} else {
					// Some but not all the new snippets are present
					$num_missing_snippets = $core_file_modification['num_mods'] - $num_new_code_snippets_present;

					if ($num_missing_snippets == 1) {
						// All but one of the snippets are missing
						if ($core_file_modification['num_mods'] == 2) {
							// A snippet has been applied but one is missing
							if ($num_previous_code_snippets_remaining > 0) {
								// At least one old snippet remains
								if ($num_previous_code_snippets_remaining == $num_missing_snippets) {
									// User may have forgotten to update the other snippet

									// @TODO Try to match up the snippet start and end blocks. In meantime, just
									// assuming that only snippet content mistakes have been made

									// Add an error message for a file with one snippet in place but either one missing
									// and/or (an) other still using old code
									if ($new_code_snippets_missing[0] == 1) {
										// The first modification is missing
										$error_desc =  TEXT_ERROR_SECOND_MOD_PRESENT_FIRST_MISSING_OLD_MOD_PRESENT;

										$extra_desc = [
											TEXT_COULD_BE_THAT_FIRST_MISSED_OLD_REMAINS,
											TEXT_COULD_BE_THAT_FIRST_MOD_MISSED_EXTRA_OLD_REMAINS
                                        ];
									} else {
										// The second modification is missing
										$error_desc =  TEXT_ERROR_FIRST_MOD_PRESENT_SECOND_MISSING_OLD_MOD_PRESENT;

										$extra_desc = [
											TEXT_COULD_BE_THAT_SECOND_MISSED_OLD_REMAINS,
											TEXT_COULD_BE_THAT_SECOND_MOD_MISSED_EXTRA_OLD_REMAINS
                                        ];
									}

									$instructions1 = sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MOD_X,
										$new_code_snippets_missing[0]);

									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => $error_desc,
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => $extra_desc,
										'instructions' => [
											$instructions1,
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								} else {
									// Add an error message for a file with one snippet in place but one missing and x
									// old snippets remaining
									if ($new_code_snippets_missing[0] == 1) {
										// The first modification is missing
										$error_desc = sprintf(
											TEXT_ERROR_SECOND_MOD_PRESENT_FIRST_MISSING_X_OLD_MODS_PRESENT,
											$num_previous_code_snippets_remaining);

										$extra_desc = [
											sprintf(TEXT_COULD_BE_THAT_FIRST_MISSED_X_OLD_REMAIN,
												$num_previous_code_snippets_remaining),
											sprintf(TEXT_COULD_BE_THAT_FIRST_MOD_MISSED_X_EXTRA_OLD_REMAIN,
												$num_previous_code_snippets_remaining)
                                        ];
									} else {
										// The second modification is missing
										$error_desc = sprintf(
											TEXT_ERROR_FIRST_MOD_PRESENT_SECOND_MISSING_X_OLD_MODS_PRESENT,
											$num_previous_code_snippets_remaining);

										$extra_desc = [
											sprintf(TEXT_COULD_BE_THAT_SECOND_MISSED_X_OLD_REMAIN,
												$num_previous_code_snippets_remaining),
											sprintf(TEXT_COULD_BE_THAT_SECOND_MOD_MISSED_X_EXTRA_OLD_REMAIN,
												$num_previous_code_snippets_remaining)
                                        ];
									}

									$instructions1 = sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MOD_X,
										$new_code_snippets_missing[0]);

									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => $error_desc,
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => $extra_desc,
										'instructions' => [
											$instructions1,
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								}
							} else {
								// Add an error message for a file with one snippet in place but one missing

								if ($new_code_snippets_missing[0] == 1) {
									// The first modification is missing
									$error_desc = TEXT_ERROR_SECOND_MOD_PRESENT_FIRST_MISSING;
								} else {
									// The second modification is missing
									$error_desc =  TEXT_ERROR_FIRST_MOD_PRESENT_SECOND_MISSING;
								}

								$this->_modified_core_files_error_messages[] = [
									'initial_desc' => $error_desc,
									'current_value' => [
										sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
											realpath($core_file_modification['path']))
                                    ],
									'instructions' => [
										sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MOD_X,
											$new_code_snippets_missing[0]),
										TEXT_ALTERNATIVELY_REPLACE_FILE_TO_APPLY_MODIFICATIONS
                                    ]
                                ];
							}
						} else {
							// Several snippets have been applied but one is missing

							// Build a description of the list of modifications which have been made successfully
							if (count($new_code_snippets_present) == 2) {
								$list_of_new_code_snippets_present =
									implode(' &amp; ', $new_code_snippets_present);
							} else {
								$list_of_new_code_snippets_present = implode(', ', $new_code_snippets_present);

								// Replace last comma with an ampersand
								$last_comma_pos = strrpos($list_of_new_code_snippets_present, ',');

								$list_of_new_code_snippets_present = substr(
									$list_of_new_code_snippets_present, 0, $last_comma_pos) . ' &amp; ' .
									substr($list_of_new_code_snippets_present, $last_comma_pos + 1,
									strlen($list_of_new_code_snippets_present) - 1);
							}

							if ($num_previous_code_snippets_remaining > 0) {
								// At least one old snippet remains
								if ($num_previous_code_snippets_remaining == $num_missing_snippets) {
									// User may have forgotten to update the final snippet

									// Add an error message for a file with x snippets in place but either one missing
									// and/or (an) other still using old code
									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => sprintf(
											TEXT_ERROR_X_MODS_PRESENT_MOD_X_MISSING_OLD_MOD_PRESENT,
											$core_file_modification['num_mods'],
											$list_of_new_code_snippets_present, $new_code_snippets_missing[0]),
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => [
											sprintf(TEXT_COULD_BE_THAT_MOD_X_MISSED_OLD_REMAINS,
												$list_of_new_code_snippets_present, $new_code_snippets_missing[0]),
											sprintf(TEXT_COULD_BE_THAT_MOD_X_MISSED_EXTRA_OLD_REMAINS,
												$list_of_new_code_snippets_present, $new_code_snippets_missing[0])
                                        ],
										'instructions' => [
											sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MOD_X,
												$new_code_snippets_missing[0]),
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								} else {
									// Add an error message for a file with x snippets in place but one missing and x
									// old snippets remaining
									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => sprintf(
											TEXT_ERROR_X_MODS_PRESENT_MOD_X_MISSING_X_OLD_MODS_PRESENT,
											$core_file_modification['num_mods'],
											$list_of_new_code_snippets_present, $new_code_snippets_missing[0],
											$num_previous_code_snippets_remaining),
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => [
											sprintf(TEXT_COULD_BE_THAT_X_MODS_MADE_X_OLD_REMAIN_MOD_X_MISSED,
												$list_of_new_code_snippets_present,
												$num_previous_code_snippets_remaining,
												$new_code_snippets_missing[0]),
											sprintf(TEXT_COULD_BE_THAT_X_MODS_MADE_MOD_MISSED_X_EXTRA_OLD_REMAIN,
												$list_of_new_code_snippets_present, $new_code_snippets_missing[0],
												$num_previous_code_snippets_remaining)
                                        ],
										'instructions' => [
											sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MOD_X,
												$new_code_snippets_missing[0]),
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								}
							} else {
								// Add an error message for a file with x snippets in place but one missing
								$this->_modified_core_files_error_messages[] = [
									'initial_desc' => sprintf(TEXT_ERROR_X_MODS_PRESENT_MOD_X_MISSING,
										$core_file_modification['num_mods'], $list_of_new_code_snippets_present,
										$new_code_snippets_missing[0]),
									'current_value' => [
										sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
											realpath($core_file_modification['path']))
                                    ],
									'instructions' => [
										sprintf(TEXT_APPLY_MISSING_MOD_X, $new_code_snippets_missing[0]),
										TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                    ]
                                ];
							}
						}
					} else {
						// 2 or more of the required snippets are missing
						$num_applied_snippets = $core_file_modification['num_mods'] - $num_missing_snippets;

						// Build a description of the list of modifications which are missing
						if (count($new_code_snippets_missing) == 2) {
							$list_of_new_code_snippets_missing = implode(' &amp; ', $new_code_snippets_missing);

							$list_of_new_code_snippets_missing_or = implode(' ' . TEXT_OR . ' ',
								$new_code_snippets_missing);
						} else {
							$list_of_new_code_snippets_missing = implode(', ', $new_code_snippets_missing);

							// Replace last comma with an ampersand
							$last_comma_pos = strrpos($list_of_new_code_snippets_missing, ',');

							$list_of_new_code_snippets_missing =
								substr($list_of_new_code_snippets_missing, 0, $last_comma_pos) . ' &amp; ' .
								substr($list_of_new_code_snippets_missing, $last_comma_pos + 1,
								strlen($list_of_new_code_snippets_missing) - 1);

							$list_of_new_code_snippets_missing_or = str_replace('&amp;', TEXT_OR,
								$list_of_new_code_snippets_missing);
						}

						if ($num_applied_snippets == 1) {
							// A snippet has been applied but the rest are missing
							if ($num_previous_code_snippets_remaining > 0) {
								// At least one old snippet remains
								if ($num_previous_code_snippets_remaining == $num_applied_snippets) {
									// User may have forgotten to update the other snippets

									// Add an error message for a file with one snippet in place but x missing and/or 1
									// other still using old code
									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => sprintf(
											TEXT_ERROR_MOD_X_PRESENT_X_MODS_MISSING_OLD_MOD_PRESENT,
											$core_file_modification['num_mods'], $new_code_snippets_present[0],
											$list_of_new_code_snippets_missing),
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => [
											sprintf(TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_OLD_REMAINS,
												$new_code_snippets_present[0], $list_of_new_code_snippets_missing,
												$list_of_new_code_snippets_missing_or),
											sprintf(TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_EXTRA_OLD_REMAINS,
												$new_code_snippets_present[0], $list_of_new_code_snippets_missing)
                                        ],
										'instructions' => [
											sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MODS_X,
												$list_of_new_code_snippets_missing),
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								} else {
									// Add an error message for a file with one snippet in place but x missing and x old
									// snippets remaining
									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => sprintf(
											TEXT_ERROR_MOD_X_PRESENT_X_MODS_MISSING_X_OLD_MODS_PRESENT,
											$core_file_modification['num_mods'], $new_code_snippets_present[0],
											$list_of_new_code_snippets_missing,
											$num_previous_code_snippets_remaining),
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => [
											sprintf(TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_X_OLD_REMAIN,
												$new_code_snippets_present[0], $list_of_new_code_snippets_missing,
												$num_previous_code_snippets_remaining,
												$list_of_new_code_snippets_missing_or),
											sprintf(TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_X_EXTRA_OLD_REMAIN,
												$new_code_snippets_present[0], $list_of_new_code_snippets_missing,
												$num_previous_code_snippets_remaining)
                                        ],
										'instructions' => [
											sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MODS_X,
												$list_of_new_code_snippets_missing),
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								}
							} else {
								// Add an error message for a file with one snippet in place but x missing
								$this->_modified_core_files_error_messages[] = [
									'initial_desc' => sprintf(TEXT_ERROR_MOD_X_PRESENT_X_MODS_MISSING,
										$core_file_modification['num_mods'], $new_code_snippets_present[0],
										$list_of_new_code_snippets_missing),
									'current_value' => [
										sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
											realpath($core_file_modification['path']))
                                    ],
									'instructions' => [
										sprintf(TEXT_APPLY_MISSING_MODS_X, $list_of_new_code_snippets_missing),
										TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                    ]
                                ];
							}
						} else {
							// Several snippets have been applied but the rest are missing

							// Build a description of the list of modifications which have been made successfully
							if (count($new_code_snippets_present) == 2) {
								$list_of_new_code_snippets_present =
									implode(' &amp; ', $new_code_snippets_present);
							} else {
								$list_of_new_code_snippets_present = implode(', ', $new_code_snippets_present);

								// Replace last comma with an ampersand
								$last_comma_pos = strrpos($list_of_new_code_snippets_present, ',');

								$list_of_new_code_snippets_present =
									substr($list_of_new_code_snippets_present, 0, $last_comma_pos) . ' &amp; ' .
									substr($list_of_new_code_snippets_present, $last_comma_pos + 1,
									strlen($list_of_new_code_snippets_present) - 1);
							}

							if ($num_previous_code_snippets_remaining > 0) {
								// At least one old snippet remains
								if ($num_previous_code_snippets_remaining == 1) {
									// Add an error message for a file with x snippets in place but x missing and an old
									// snippet remaining
									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => sprintf(
											TEXT_ERROR_MODS_X_PRESENT_MODS_X_MISSING_OLD_MOD_PRESENT,
											$core_file_modification['num_mods'],
											$list_of_new_code_snippets_present,
											$list_of_new_code_snippets_missing),
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => [
											sprintf(TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_OLD_REMAINS,
												$list_of_new_code_snippets_present,
												$list_of_new_code_snippets_missing,
												$list_of_new_code_snippets_missing_or),
											sprintf(TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_EXTRA_OLD_REMAINS,
												$list_of_new_code_snippets_present,
												$list_of_new_code_snippets_missing)
                                        ],
										'instructions' => [
											sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MODS_X,
												$list_of_new_code_snippets_missing),
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								} elseif ($num_previous_code_snippets_remaining == $num_missing_snippets) {
									// Same number of missing snippets as old snippets. User may have forgotten to
									// update the other snippets

									// Add an error message for a file with x snippets in place, but x are missing and/or
									// x others still using old code
									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => sprintf(
											TEXT_ERROR_MODS_X_PRESENT_MODS_X_MISSING_X_OLD_MODS_PRESENT,
											$core_file_modification['num_mods'],
											$list_of_new_code_snippets_present, $list_of_new_code_snippets_missing,
											$num_previous_code_snippets_remaining),
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => [
											sprintf(
												TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_SAME_X_OLD_REMAIN,
												$list_of_new_code_snippets_present,
												$list_of_new_code_snippets_missing,
												$num_previous_code_snippets_remaining,
												$list_of_new_code_snippets_missing),
											sprintf(
												TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_EXTRA_X_OLD_REMAIN,
												$list_of_new_code_snippets_present,
												$list_of_new_code_snippets_missing,
												$num_previous_code_snippets_remaining)
                                        ],
										'instructions' => [
											sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MODS_X,
												$list_of_new_code_snippets_missing),
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								} else {
									// Add an error message for a file with x snippets in place but x missing and x old
									// snippets remaining (more old snippets remaining than snippets which are
									// missing - very unlikely but meh)
									$this->_modified_core_files_error_messages[] = [
										'initial_desc' => sprintf(
											TEXT_ERROR_MODS_X_PRESENT_MODS_X_MISSING_X_OLD_MODS_PRESENT,
											$core_file_modification['num_mods'],
											$list_of_new_code_snippets_present,
											$list_of_new_code_snippets_missing,
											$num_previous_code_snippets_remaining),
										'current_value' => [
											sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
												realpath($core_file_modification['path']))
                                        ],
										'extra_desc' => [
											sprintf(TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_X_OLD_REMAIN,
												$list_of_new_code_snippets_present,
												$list_of_new_code_snippets_missing,
												$num_previous_code_snippets_remaining),
											sprintf(
												TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_EXTRA_X_OLD_REMAIN,
												$list_of_new_code_snippets_present,
												$list_of_new_code_snippets_missing,
												$num_previous_code_snippets_remaining)
                                        ],
										'instructions' => [
											sprintf(TEXT_CHECK_FILE_APPLY_MISSING_MODS_X,
												$list_of_new_code_snippets_missing),
											TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                        ]
                                    ];
								}
							} else {
								// Add an error message for a file with x snippets in place but x missing
								$this->_modified_core_files_error_messages[] = [
									'initial_desc' => sprintf(TEXT_ERROR_X_MODS_PRESENT_X_MODS_MISSING,
										$core_file_modification['num_mods'], $list_of_new_code_snippets_present,
										$list_of_new_code_snippets_missing),
									'current_value' => [
										sprintf(TEXT_THE_PATH_TO_THE_FILE_IS,
											realpath($core_file_modification['path']))
                                    ],
									'instructions' => [
										sprintf(TEXT_APPLY_MISSING_MODS_X, $list_of_new_code_snippets_missing),
										TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                                    ]
                                ];
							}
						}
					}
				}
			} else {
				// Correct number of new snippets present. Make sure no old snippets remain
				if ($core_file_modification['num_mods'] == 1) {
					if ($num_previous_code_snippets_remaining == 1) {
						// Add an error message for a file with its snippet in place but one old snippet remaining
						$this->_modified_core_files_error_messages[] = [
							'initial_desc' => TEXT_ERROR_MOD_PRESENT_OLD_MOD_REMAINS,
							'current_value' => [
								sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                            ],
							'instructions' => [
								TEXT_REMOVE_OLD_MOD,
								TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                            ]
                        ];
					} elseif ($num_previous_code_snippets_remaining > 0) {
						// Add an error message for a file with its snippet in place but x old snippet remaining
						$this->_modified_core_files_error_messages[] = [
							'initial_desc' => sprintf(TEXT_ERROR_MOD_PRESENT_X_OLD_MODS_REMAIN,
								$num_previous_code_snippets_remaining),
							'current_value' => [
								sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                            ],
							'instructions' => [
								TEXT_REMOVE_OLD_MOD,
								TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                            ]
                        ];
					}
				} else {
					if ($num_previous_code_snippets_remaining == 1) {
						// Add an error message for a file with its x snippets in place but one old snippet remaining
						$this->_modified_core_files_error_messages[] = [
							'initial_desc' => sprintf(TEXT_ERROR_X_MODS_PRESENT_OLD_MOD_REMAINS,
								$core_file_modification['num_mods']),
							'current_value' => [
								sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                            ],
							'instructions' => [
								TEXT_REMOVE_OLD_MOD,
								TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                            ]
                        ];
					} elseif ($num_previous_code_snippets_remaining > 0) {
						// Add an error message for a file with its x snippets in place but x old snippet remaining
						$this->_modified_core_files_error_messages[] = [
							'initial_desc' => sprintf(TEXT_ERROR_X_MODS_PRESENT_X_OLD_MODS_REMAIN,
								$core_file_modification['num_mods'], $num_previous_code_snippets_remaining),
							'current_value' => [
								sprintf(TEXT_THE_PATH_TO_THE_FILE_IS, realpath($core_file_modification['path']))
                            ],
							'instructions' => [
								TEXT_REMOVE_OLD_MOD,
								TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS
                            ]
                        ];
					}
				}
			}
		}
	}

	// }}}


	// {{{ _checkOldFilesDirsRemaining()

	/**
	 * Examines the store's files to check if any files/directories for an old version of Ceon URI Mapping remain
	 * in the store's filesystem. Some of these files can cause clashes and therefore PHP errors. Adds the
	 * appropriate error messages to the main error messages property.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _checkOldFilesDirsRemaining(): void
    {
		// Main variable holds the list of files to check
		$old_files_dirs = [];

		$old_files_dirs[] = [
			'file' => DIR_WS_CLASSES . 'class.String.php',
        ];

		$old_files_dirs[] = [
			'file' => DIR_WS_CLASSES . 'class.Transliteration.php',
        ];

		$old_files_dirs[] = [
			'dir' => DIR_WS_CLASSES . 'transliteration',
        ];

		$old_files_dirs[] = [
			'file' => DIR_WS_FUNCTIONS . 'extra_functions/ceon_uri_mapping.php',
        ];

		$old_files_dirs[] = [
			'file' => 'ceon_uri_mapping_auto_upgrade.php',
        ];

		$old_files_dirs[] = [
			'file' => DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.String.php',
        ];

		$old_files_dirs[] = [
			'file' => DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.StringHelper.php',
        ];

		$old_files_dirs[] = [
			'file' => DIR_FS_CATALOG . DIR_WS_INCLUDES . 'extra_datafiles/ceon_uri_mapping_config.php',
        ];

		$old_files_dirs[] = [
			'file' => DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'ceon_uri_mapping_functions.php',
        ];

		$old_files_dirs[] = [
			'file' => DIR_FS_CATALOG . DIR_WS_INCLUDES . 'init_includes/init_ceon_uri_mapping.php',
        ];

		$old_files_dirs[] = [
			'file' => DIR_FS_CATALOG . DIR_WS_INCLUDES . 'templates/template_default/jscript/' .
				'jscript_ceon_uri_mapping_canonical_link_header_tag.php',
        ];

		foreach ($old_files_dirs as $old_file_or_dir) {
			// Attempt to remove the file/directory
			$path = $old_file_or_dir['file'] ?? $old_file_or_dir['dir'];

			if (file_exists($path)) {
				if (!is_dir($path)) {
					@unlink($path);
				} else {
					$this->_deleteDir($path);
				}

				if (file_exists($path)) {
					$this->_old_files_dirs_error_messages[] = [
						'initial_desc' => (isset($old_file_or_dir['file']) ?
							TEXT_ERROR_OLD_FILE_REMAINS : TEXT_ERROR_OLD_DIR_REMAINS),
						'current_value' => [
							sprintf((isset($old_file_or_dir['file']) ?
								TEXT_THE_PATH_TO_THE_FILE_IS : TEXT_THE_PATH_TO_THE_DIR_IS), realpath($path))
                        ],
						'instructions' => [
							(isset($old_file_or_dir['file']) ? TEXT_DELETE_FILE : TEXT_DELETE_DIR)
                        ]
                    ];
				}
			}
		}
	}

	// }}}


	// {{{ _deleteDir()

	/**
	 * Simply deletes a directory, deleting all files and subdirectories within first, as required by PHP's file
	 * functions.
	 *
	 * @access  protected
	 * @param  string  $path   The path to the directory to be deleted.
	 * @return  void
	 */
	protected function _deleteDir(string $path): void
    {
		$dir_obj = dir($path);

		while ($current_file = $dir_obj->read()) {
			if ($current_file != '.' && $current_file != '..') {
				$current_file = $path . '/' . $current_file;

				if (!is_dir($current_file)) {
					@unlink($current_file);
				} else {
					$this->_deleteDir($current_file);
				}
			}
		}

		$dir_obj->close();

		@rmdir($path);
	}

	// }}}


	// {{{ getOutput()

	/**
	 * Builds and returns the output for this instance.
	 *
	 * @access  public
	 * @return  string|null    The HTML for the installation check's output.
	 */
	public function getOutput(): ?string
    {
		$this->_buildOutput();

		return $this->_output;
	}

	// }}}


	// {{{ _buildOutput()

	/**
	 * Builds the interface for the installation check.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _buildOutput(): void
    {
		// Build the panel this page uses, adding it to the list of panels
		$this->_buildMainPanel();

		// Build the actual output
		$this->_buildTabbedPanelMenu();

		$this->_buildPanels();

		$this->_buildFooter();
	}

	// }}}


	// {{{ _getSelectedPanelID()

	/**
	 * Gets the ID of the panel which should be selected by default when the tabbed panel admin interface is built.
	 *
	 * @access  protected
	 * @return  string|null    The ID of the selected panel.
	 */
	protected function _getSelectedPanelID(): ?string
    {
        return 'installation-check-panel';
	}

	// }}}


	// {{{ _buildMainPanel()

	/**
	 * Builds a panel with a title/intro and, if any errors were encountered in the installation checks,
	 * subpanel(s) with a list of messages about these installation issues. Also builds a subpanel with example
	 * rewrite rule for the store. Adds the panel to the list of panels.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _buildMainPanel(): void
    {
		$panel = '<fieldset id="installation-check-panel" class="CeonPanel">' . "\n";

		$panel .= '<legend class="DisplayNone">' . TEXT_INSTALLATION_CHECK . '</legend>' . "\n";

		$num_config_files_error_messages = count($this->_config_files_error_messages);

		$num_modified_core_files_error_messages =
			count($this->_modified_core_files_error_messages);

		$num_old_files_dirs_error_messages = count($this->_old_files_dirs_error_messages);

		// Build the intro for the panel
		$panel .= '<div id="intro">' . "\n";

		if ($num_config_files_error_messages == 0 && $num_modified_core_files_error_messages == 0 &&
				$num_old_files_dirs_error_messages == 0) {
			$panel .= '<h2 class="NoErrors">' . TEXT_NO_ERRORS_FOUND_TITLE . '</h2>' . "\n";
			$panel .= '<p class="NoErrors">' . TEXT_NO_ERRORS_FOUND . '</p>' . "\n";
			$panel .= '<p class="NoErrors">' . TEXT_NO_CHECKS_MADE_FOR_REWRITE_RULE . '</p>' . "\n";

		} elseif ($num_config_files_error_messages > 0) {
			if ($num_config_files_error_messages == 1 && $num_modified_core_files_error_messages == 0) {
				$panel .= '<h2 class="ErrorsIntro">' . TEXT_ERROR_FOUND_TITLE . '</h2>' . "\n";
			} else {
				$panel .= '<h2 class="ErrorsIntro">' . TEXT_ERRORS_FOUND_TITLE . '</h2>' . "\n";
			}

			if ($num_config_files_error_messages == 1) {
				$panel .= '<p class="ErrorsIntro">' .
					TEXT_CONFIG_FILE_ERROR_SO_NO_REWRITE_RULE_OUTPUT . '</p>' . "\n";
				$panel .= '<p class="ErrorsIntro">' . TEXT_PLEASE_FIX_CONFIG_FILE_ERRORS . '</p>' .
					"\n";
			} else {
				$panel .= '<p class="ErrorsIntro">' . sprintf(
					TEXT_CONFIG_FILE_ERRORS_SO_NO_REWRITE_RULE_OUTPUT,
					$num_config_files_error_messages) . '</p>' . "\n";
				$panel .= '<p class="ErrorsIntro">' . TEXT_PLEASE_FIX_CONFIG_FILE_ERROR . '</p>' .
					"\n";
			}
		} else {
			// Problems have been found with the modified core files or with old files remaining from a previous
			// version of the software, but the configuration files are okay, so the example rewrite rule panel can
			// be built and shown... link to it for the user's convenience
			if (($num_modified_core_files_error_messages + $num_old_files_dirs_error_messages) == 1) {
				$panel .= '<h2 class="ErrorsIntro">' . TEXT_ERROR_FOUND_TITLE . '</h2>' . "\n";
			} else {
				$panel .= '<h2 class="ErrorsIntro">' . TEXT_ERRORS_FOUND_TITLE . '</h2>' . "\n";
			}

			if ($num_modified_core_files_error_messages == 1) {
				$panel .= '<p class="ErrorsIntro">' . TEXT_MODIFIED_CORE_FILE_ERROR . '</p>' . "\n";
				$panel .= '<p class="ErrorsIntro">' . TEXT_PLEASE_FIX_MODIFIED_CORE_FILE_ERROR . '</p>' . "\n";
			} elseif ($num_modified_core_files_error_messages > 1) {
				$panel .= '<p class="ErrorsIntro">' . sprintf(TEXT_MODIFIED_CORE_FILE_ERRORS,
					$num_modified_core_files_error_messages) . '</p>' . "\n";
				$panel .= '<p class="ErrorsIntro">' . TEXT_PLEASE_FIX_MODIFIED_CORE_FILE_ERRORS . '</p>' . "\n";
			}

			if ($num_old_files_dirs_error_messages == 1) {
				$panel .= '<p class="ErrorsIntro">' . TEXT_OLD_FILE_DIR_ERROR . '</p>' . "\n";
				$panel .= '<p class="ErrorsIntro">' . TEXT_PLEASE_FIX_OLD_FILE_DIR_ERROR . '</p>' . "\n";
			} elseif ($num_old_files_dirs_error_messages > 1) {
				$panel .= '<p class="ErrorsIntro">' . sprintf(TEXT_OLD_FILE_DIR_ERRORS,
					$num_old_files_dirs_error_messages) . '</p>' . "\n";
				$panel .= '<p class="ErrorsIntro">' . TEXT_PLEASE_FIX_OLD_FILE_DIR_ERRORS . '</p>' . "\n";
			}

			$panel .= '<ul id="CeonSubPanelMenu">' . "\n";

			$panel .= '<li><a href="#example-rewrite-rule">' . TEXT_EXAMPLE_REWRITE_RULE .
				'</a></li>' . "\n";

			$panel .= '</ul>' . "\n";
		}

		$panel .= '</div>' . "\n";

		// Output the information about any problems found within the store's configuration files
		if ($num_config_files_error_messages > 0) {

			$panel .= '<fieldset id="configuration-check-errors">';

			$panel .= '<legend>' . TEXT_STORE_CONFIGURATION_FILES_CHECK . '</legend>' . "\n";

			foreach ($this->_config_files_error_messages as $error_message) {
				$panel .= '<div class="InstallationError">' . "\n";

				$panel .= '<h3 class="ErrorInitialDesc">' . $error_message['initial_desc'] . "</h3>\n";

				if (isset($error_message['current_value'])) {
					foreach ($error_message['current_value'] as $current_value) {
						$panel .= '<p class="ErrorCurrentValue">' . $current_value . "</p>\n";
					}
				}

				if (isset($error_message['extra_desc'])) {
					$panel .= '<ul class="ErrorExtraDesc">' . "\n";

					foreach ($error_message['extra_desc'] as $extra_desc) {
						$panel .= '<li>' . $extra_desc . "</li>\n";
					}

					$panel .= '</ul>' . "\n";
				}

				if (count($error_message['instructions']) > 0) {
					$panel .= '<ul class="ErrorInstructions">' . "\n";

					foreach ($error_message['instructions'] as $instruction) {
						$panel .= '<li>' . $instruction . "</li>\n";
					}

					$panel .= '</ul>' . "\n";
				}

				$panel .= '</div>' . "\n";
			}

			$panel .= '</fieldset>' . "\n";
		}


		// Output the information about any problems found when checking the modified core files
		if ($num_modified_core_files_error_messages > 0) {

			$panel .= '<fieldset id="modified-core-files-errors">';

			$panel .= '<legend>' . TEXT_MODIFIED_CORE_FILES_CHECK . '</legend>' . "\n";

			foreach ($this->_modified_core_files_error_messages as $error_message) {
				$panel .= '<div class="InstallationError">' . "\n";

				$panel .= '<h3 class="ErrorInitialDesc">' .
					$error_message['initial_desc'] . "</h3>\n";

				if (isset($error_message['current_value'])) {
					foreach ($error_message['current_value'] as $current_value) {
						$panel .= '<p class="ErrorCurrentValue">' . $current_value . "</p>\n";
					}
				}

				if (isset($error_message['extra_desc'])) {
					$panel .= '<ul class="ErrorExtraDesc">' . "\n";

					foreach ($error_message['extra_desc'] as $extra_desc) {
						$panel .= '<li>' . $extra_desc . "</li>\n";
					}

					$panel .= '</ul>' . "\n";
				}

				if (count($error_message['instructions']) > 0) {
					$panel .= '<ul class="ErrorInstructions">' . "\n";

					foreach ($error_message['instructions'] as $instruction) {
						$panel .= '<li>' . $instruction . "</li>\n";
					}

					$panel .= '</ul>' . "\n";
				}

				$panel .= '</div>' . "\n";
			}

			$panel .= '</fieldset>' . "\n";
		}

		// Output the information about any problems found when checking the old files or directories
		if ($num_old_files_dirs_error_messages > 0) {

			$panel .= '<fieldset id="old-files-dirs-errors">';

			$panel .= '<legend>' . TEXT_MODIFIED_OLD_FILES_DIRS_CHECK . '</legend>' . "\n";

			foreach ($this->_old_files_dirs_error_messages as $error_message) {
				$panel .= '<div class="InstallationError">' . "\n";

				$panel .= '<h3 class="ErrorInitialDesc">' .
					$error_message['initial_desc'] . "</h3>\n";

				if (isset($error_message['current_value'])) {
					foreach ($error_message['current_value'] as $current_value) {
						$panel .= '<p class="ErrorCurrentValue">' . $current_value . "</p>\n";
					}
				}

				if (count($error_message['instructions']) > 0) {
					$panel .= '<ul class="ErrorInstructions">' . "\n";

					foreach ($error_message['instructions'] as $instruction) {
						$panel .= '<li>' . $instruction . "</li>\n";
					}

					$panel .= '</ul>' . "\n";
				}

				$panel .= '</div>' . "\n";
			}

			$panel .= '</fieldset>' . "\n";
		}

		// Build the example rewrite rule sub panel if possible
		if ($num_config_files_error_messages == 0) {
			$panel .= '<fieldset id="example-rewrite-rule">';

			$panel .= '<legend>' . TEXT_EXAMPLE_REWRITE_RULE . '</legend>' . "\n";

			$panel .= $this->_buildExampleRewriteRulePanelContent();

			$panel .= '</fieldset>' . "\n";
		}


		$panel .= '</fieldset>' . "\n";

		$this->_addPanel('installation-check-panel', TEXT_INSTALLATION_CHECK, null, $panel);
	}

	// }}}


	// {{{ _buildExampleRewriteRulePanelContent()

	/**
	 * Analyses the store's settings and builds the example rewrite rule for the server and the HTML content for
	 * the example rewrite rule panel for the store.
	 *
	 * @access  protected
	 * @return  string    The HTML for the content of the Example Rewrite Rule panel.
	 */
	protected function _buildExampleRewriteRulePanelContent(): string
    {
		$content = '';

		// Determine what server type is being used, so that the most appropriate information can be displayed.
		// Types which can be identified:
		//
		// apache: Apache (Then assumed to have mod_rewrite)
		// iis-url-rewrite: M$ IIS with URL Rewrite module
		// iis-isapi-rewrite: M$ IIS with ISAPI Rewrite module
		// nginx:
		$server_type = '';

		if (!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = '';
		}

		if (!isset($_SERVER['SERVER_SOFTWARE'])) {
			$_SERVER['SERVER_SOFTWARE'] = '';
		}

		if (empty($_SERVER['REQUEST_URI']) || str_contains($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS')) {
			if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
				// IIS URL Rewrite module is being used
				$server_type = 'iis-url-rewrite';

			} elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
				// IIS ISAPI_Rewrite module is being used
				$server_type = 'iis-isapi-rewrite';
			}
		} elseif (str_contains(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache')) {
			$server_type = 'apache';

		} elseif (str_contains(strtolower($_SERVER['SERVER_SOFTWARE']), 'nginx')) {
			$server_type = 'nginx';
		}

		// Build the example rule
		if ($server_type == 'nginx') {
			// The rule for nginx is very simple so is built here
			$this->_guestimated_rewrite_rule = 'try_files $uri /$uri /index.php$is_args$args;';
		} else {
			$this->_buildBasicRewriteRule();

			$this->_buildRewriteRuleWithGuestimatedExclusions();
		}


		$content .= '<p>' . TEXT_EXAMPLE_REWRITE_RULE_BUILT_INTRO . '</p>' . "\n";

		$content .= '<p>' . TEXT_EXAMPLE_REWRITE_RULE_GOOD_BASIS . '</p>' . "\n";

		// Fall back to showing instructions for Apache if the server type cannot be determined
		if ($server_type == 'apache' || $server_type == '') {

			if ($server_type == 'apache') {
				$content .= '<h2>' . TEXT_APACHE_TITLE . '</h2>' . "\n";

				$content .= '<p>' . TEXT_SERVER_APPEARS_TO_BE_APACHE . '</p>' . "\n";
			}

			$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_IN_HTACCESS_FILE_OR_VIRTUALHOST_DIRECTIVE . '</p>' . "\n";

			$content .= '<h3>' . TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_TITLE . '</h3>' . "\n";

			if (strlen(DIR_WS_CATALOG) > 1 &&
                !str_contains(strtolower(DIR_FS_CATALOG), strtolower(DIR_WS_CATALOG))) {
				$content .= '<p>' . sprintf(TEXT_ERROR_DIR_FS_CATALOG_PROBLEM, DIR_WS_CATALOG, DIR_FS_CATALOG) .
					'</p>' . "\n";

				$content .= '<p>' . TEXT_ERROR_DIR_FS_CATALOG_PROBLEM_POSSIBLE_REASON . "</p>\n";

			} else {
				// Build the path to the store's root .htaccess file
				$htaccess_file = substr(DIR_FS_CATALOG, 0, strlen(DIR_FS_CATALOG) - strlen(DIR_WS_CATALOG)) .
					'/.htaccess';

				if (file_exists($htaccess_file)) {
					$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_INFO_EXISTS . '</p>' . "\n";
				} else {
					$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_INFO . '</p>' . "\n";
				}

				$content .= '<p><code><strong>' . $htaccess_file . '</strong></code></p>' . "\n";

				if (DIR_WS_CATALOG != '/') {
					$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_INFO_SUBDIR . '</p>' . "\n";
				}
			}


			$content .= '<h3>' . TEXT_PLACE_REWRITE_RULE_VIRTUALHOST_DIRECTIVE_TITLE . '</h3>' . "\n";

			$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_VIRTUALHOST_DIRECTIVE_INFO . '</p>' . "\n";

			$content .= '<p>' . sprintf(TEXT_PLACE_REWRITE_RULE_VIRTUALHOST_DIRECTIVE_INSTRUCTIONS,
				substr(DIR_WS_CATALOG, 1, strlen(DIR_WS_CATALOG) - 1) . 'index.php', 'RewriteRule .* /' .
				substr(DIR_WS_CATALOG, 1, strlen(DIR_WS_CATALOG) - 1) . 'index.php [QSA,L]') . '</p>' . "\n";

		} elseif ($server_type == 'iis-isapi-rewrite') {
			$content .= '<h2>' . TEXT_IIS_ISAPI_REWRITE_TITLE . '</h2>' . "\n";

			$content .= '<p>' . TEXT_SERVER_APPEARS_TO_BE_IIS_ISAPI_REWRITE . '</p>' . "\n";

			$content .= '<h3>' . TEXT_PLACE_REWRITE_RULE_ISAPI_REWRITE_LITE_VERSION_TITLE . '</h3>' . "\n";

			$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_IN_ISAPI_REWRITE_GLOBAL_HTTPD_CONF_INFO . '</p>' . "\n";

			$content .= '<h3>' . TEXT_PLACE_REWRITE_RULE_ISAPI_REWRITE_FULL_VERSION_DIRECTIVE_TITLE . '</h3>' .
				"\n";

			// Build the path to the store's root .htaccess file
			$htaccess_file = substr(DIR_FS_CATALOG, 0, strlen(DIR_FS_CATALOG) - strlen(DIR_WS_CATALOG)) .
				'/.htaccess';

			if (file_exists($htaccess_file)) {
				$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_IN_ISAPI_REWRITE_LOCAL_HTACCESS_INFO_EXISTS . '</p>' .
					"\n";
			} else {
				$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_IN_ISAPI_REWRITE_LOCAL_HTACCESS_INFO . '</p>' . "\n";
			}

			$content .= '<p><code><strong>' . $htaccess_file . '</strong></code></p>' . "\n";

			if (DIR_WS_CATALOG != '/') {
				$content .= '<p>' . TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_INFO_SUBDIR . '</p>' . "\n";
			}
		} elseif ($server_type == 'iis-url-rewrite') {
			$content .= '<h2>' . TEXT_IIS_URL_REWRITE_TITLE . '</h2>' . "\n";

			$content .= '<p>' . TEXT_SERVER_APPEARS_TO_BE_IIS_URL_REWRITE . '</p>' . "\n";

			$content .= '<h3>' . TEXT_IMPORT_REWRITE_RULE_IIS_URL_REWRITE_TITLE . '</h3>' . "\n";

			$content .= '<p>' . TEXT_IMPORT_REWRITE_RULE_IIS_URL_REWRITE_INFO . '</p>' . "\n";

			$content .= '<p>' . TEXT_IMPORT_REWRITE_RULE_IIS_URL_REWRITE_HINTS . '</p>' . "\n";

		} elseif ($server_type == 'nginx') {
			$content .= '<h2>' . TEXT_NGINX_TITLE . '</h2>' . "\n";

			$content .= '<p>' . TEXT_SERVER_APPEARS_TO_BE_NGINX . '</p>' . "\n";

			$content .= '<h3>' . TEXT_PLACE_REWRITE_DIRECTIVE_NGINX_TITLE . '</h3>' . "\n";

			$content .= '<p>' . sprintf(TEXT_PLACE_REWRITE_DIRECTIVE_NGINX_INFO, DIR_WS_CATALOG, DIR_WS_CATALOG) .
				'</p>' . "\n";
		}

		if ($server_type != 'nginx') {
			$content .= '<h2>' . TEXT_REWRITE_RULE_PROBLEMS_TITLE . '</h2>' . "\n";

			$content .= '<p>' . TEXT_REWRITE_RULE_ERRORS_INTRO . '</p>' . "\n";

			$content .= '<p>' . sprintf(TEXT_REWRITE_RULE_ERROR_TRY_ADDING_SLASH,
				substr(DIR_WS_CATALOG, 1, strlen(DIR_WS_CATALOG) - 1) . 'index.php', 'RewriteRule .* /' .
				substr(DIR_WS_CATALOG, 1, strlen(DIR_WS_CATALOG) - 1) . 'index.php [QSA,L]') . '</p>' . "\n";
		}

		// Add JavaScript function for quick and easy selection of the rule's content
		$content .= <<<SELECT_AND_COPY_JS
<script>
<!--
function HighlightAll(field) {
	var el = eval("document." + field);
	el.focus();
	el.select();
	
	if (document.all){
		therange = el.createTextRange();
		therange.execCommand("Copy")
	}
}
//-->
</script>
SELECT_AND_COPY_JS;

		$content .= '<form name="example_rewrite_rule_source" action="javascript:return false;">' . "\n";

		$content .= '<textarea name="example_rewrite_rule" id="example-rewrite-rule-textarea"' .
			' rows="' . (substr_count($this->_guestimated_rewrite_rule, "\n") + 3) . '" cols="103">' .
			htmlentities($this->_guestimated_rewrite_rule) . '</textarea>' . "\n";

		$content .= '<p class="SelectAndCopy"><a href="javascript:HighlightAll(' .
			"'example_rewrite_rule_source.example_rewrite_rule'" . ')">' . TEXT_SELECT_ALL_AND_COPY . '</a></p>' .
			"\n";

		$content .= '</form>' . "\n";

		if ($server_type == 'nginx') {
			$content .= '<p id="nginx-example-intro">' . TEXT_NGINX_EXAMPLE_INTRO . '</p>' . "\n";

			$content .= '<pre id="nginx-example">' . sprintf(TEXT_NGINX_EXAMPLE, DIR_WS_CATALOG,
				$this->_guestimated_rewrite_rule) . '</pre>' . "\n";
		}

		return $content;
	}

	// }}}


	// {{{ _buildBasicRewriteRule()

	/**
	 * Builds an example rewrite rule for the store which has only the essential Zen Cart exclusions.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _buildBasicRewriteRule(): void
    {
		$basic_rule = '## BEGIN CEON URI MAPPING REWRITE RULE

RewriteEngine On' . "\n\n";

		if (DIR_WS_CATALOG != '/') {
			$basic_rule .= '# ONLY rewrite URIs beginning with ' . DIR_WS_CATALOG . "\n";
			$basic_rule .= 'RewriteCond %{REQUEST_URI} ^' . DIR_WS_CATALOG . ' [NC]' . "\n";
		}

		$dir_ws_admin = DIR_WS_ADMIN;

		while (str_ends_with($dir_ws_admin, '/')) {
			$dir_ws_admin = substr($dir_ws_admin, 0, strlen($dir_ws_admin) - 1);
		}

		$basic_rule .= "# Don't rewrite any URIs ending with a file extension (ending with .[xxxxx])
RewriteCond %{REQUEST_URI} !\.[a-z]{2,5}$ [NC]
# Don't rewrite any URIs for some, popular specific file format extensions,
#   which are not covered by main file extension condition above
RewriteCond %{REQUEST_URI} !\.(mp3|mp4|h264|woff2)$ [NC]
# Don't rewrite admin directory
RewriteCond %{REQUEST_URI} !^" . $dir_ws_admin . " [NC]
# Don't rewrite editors directory
RewriteCond %{REQUEST_URI} !^" . DIR_WS_CATALOG . "editors/ [NC]
# Handle all other URIs using Zen Cart (its index.php)
RewriteRule .* " . substr(DIR_WS_CATALOG, 1, strlen(DIR_WS_CATALOG) - 1) . 'index.php [QSA,L]

## END CEON URI MAPPING REWRITE RULE';

		$this->_basic_rewrite_rule = $basic_rule;
	}

	// }}}


	// {{{ _buildRewriteRuleWithGuestimatedExclusions()

	/**
	 * Builds an example rewrite rule for the store which has the essential Zen Cart exclusions as well as
	 * exclusions for any folders that could be broken by a fairly inclusive rule such as the basic one.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _buildRewriteRuleWithGuestimatedExclusions(): void
    {
		// Base the rule on the basic rule
		$guestimated_rule = $this->_basic_rewrite_rule;

		// Add a list of less popular file extensions
		$additional_file_extensions_condition = "# Don't rewrite any URIs for some specific file format extensions,
#   which are not covered by main file extension condition above
#   Uncomment the following line to apply this condition! (Remove the # at the start of the next line)
#RewriteCond %{REQUEST_URI} !\.(3gp|3g2|h261|h263|mj2|mjp2|mp4v|mpg4|m1v|m2v|m4u|f4v|m4v|3dml)$ [NC]";

		$guestimated_rule = str_replace('h264)$ [NC]', 'h264)$ [NC]' . "\n" .
			$additional_file_extensions_condition, $guestimated_rule);

		// Check the folders in the Zen Cart installation, to see if they should be excluded
		$exclude_dirs = [];

		// Variable holds the list of folders expected to be found in a Zen Cart installation, which shouldn't need
		// to be excluded
		$zen_cart_dirs = [
			str_replace('/', '', str_replace(DIR_WS_CATALOG, '', DIR_WS_ADMIN)),
			'cache',
			'docs',
			'download',
			'editors',
			'email',
			'extras',
			'images',
			'includes',
			'media',
			'pub'
        ];

		// Variable holds list of some third party module's folders, which shouldn't need to be excluded (any .php
		// files within them will be excluded by another rule, so saves processing time to have fewer conditions in
		// the rewrite rule)
		$third_party_module_dirs = [
			'facebox',
			'fec',
			'module_version',
			'plugins',
			'quick_checkout'
        ];

		// Scan the list of files in the Zen Cart directory, checking for some renamed directories (like a renamed
		// installation directory) by looking for files expected to be found within those directories. All other
		// directories are candidates for exclusion rule
		$dir_name = DIR_FS_CATALOG;

		if ($search_dir = opendir($dir_name)) {
			// Open directory for reading
			while ($current_file = readdir($search_dir)) {
				// Make sure it's not the dots
				if ($current_file != '.' && $current_file != '..') {
					// Build file/directory name
					$full_path = $dir_name;

					$full_path = str_replace('\\', '/', $full_path);

					if (!str_ends_with($dir_name, '/')) {
						$full_path .= '/';
					}

					$full_path .= $current_file;

					if (is_dir($full_path) && !in_array($current_file, $zen_cart_dirs) &&
							!in_array($current_file, $third_party_module_dirs)) {
						// Attempt to determine if this is a renamed installation folder
						if (file_exists($full_path . '/' . 'popup_help_screen.php')) {
							// Is likely to be the ZC installation directory
							continue;
						}

						$exclude_dirs[] = $current_file;
					}
				}
			}
		}

		$exclude_dir_conditions = '';

		foreach ($exclude_dirs as $exclude_dir) {
			$exclude_dir_conditions .= "# Don't rewrite " . $exclude_dir . ' directory' . "\n";

			// Make directory name compatible with rewrite rule format
			$exclude_dir = preg_quote($exclude_dir);

			$exclude_dir = str_replace(' ', '\ ', $exclude_dir);

			$exclude_dir = str_replace('\\ ', '\ ', $exclude_dir);

			$exclude_dir_conditions .=
				'RewriteCond %{REQUEST_URI} !^' . DIR_WS_CATALOG . $exclude_dir . '/ [NC]' . "\n";
		}

		$exclude_dir_conditions = trim($exclude_dir_conditions);

		if (strlen($exclude_dir_conditions) > 0) {
			$guestimated_rule = str_replace('editors/ [NC]', 'editors/ [NC]' . "\n" . $exclude_dir_conditions,
				$guestimated_rule);
		}

		$this->_guestimated_rewrite_rule = $guestimated_rule;
	}

	// }}}
}

// }}}
