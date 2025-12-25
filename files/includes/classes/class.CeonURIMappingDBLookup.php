<?php
/**
 * Ceon URI Mapping URI DB Lookup Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingDBLookup.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

// {{{ CeonURIMappingDBLookup

/**
 * Base class for Ceon URI Mapping which provides some shared database functionality. This functionality has been
 * abstracted so that differing database implementations can be used simply by using alternative versions of this
 * file.
 *
 * @package     ceon_uri_mapping
 * @abstract
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingDBLookup
{
	// {{{ Class Constructor

	/**
	 * Creates a new instance of the CeonURIMappingDBLookup class. Not intended to be used as this is an abstract
	 * class.
	 *
	 * @access  public
	 */
	public function __construct()
	{

	}

	// }}}


	// {{{ getURIMappingsResultset()

	/**
	 * Performs a query against the URI mappings database. Use of this method abstracts the calling code from the
	 * actual implementation of the database's structure.
	 *
	 * @access  public
	 * @param  array|string  $columns_to_retrieve   The column(s) to retrieve. Either an array of column names or
	 *                                                a single column name.
	 * @param  array  $selections   An associative array of column names and values to match for these columns.
	 *                                  A set of values can be grouped with OR by specifying an array of values for
	 *                                  the value.
	 * @param  string|null  $order_by   A SQL string to be used to order the resultset.
	 * @param  int|string|null  $limit      A SQL string to be used to limit the resultset. Although all uses appear to be as 1 integer
	 * @param  string|null  $group_by   A SQL string to be used to group the resultset.
	 * @return  queryFactoryResult $db   A Zen Cart database resultset.
	 */
	public function getURIMappingsResultset(array|string $columns_to_retrieve, array $selections, ?string $order_by = null, int|string|null $limit = null, ?string $group_by = null): queryFactoryResult
    {
		global $db;

		if (is_array($columns_to_retrieve)) {
			$columns_to_retrieve = implode(', ', $columns_to_retrieve);
		}

		$selection_string = '';

		$num_selection_columns = count($selections);

		$column_name_i = 0;

		foreach ($selections as $column_name => $column_value) {
			if (is_array($column_value)) {
				// The value is an array of values so create an ORed group
				$num_column_values = count($column_value);

				$selection_string .= '(' . "\n";

				for ($column_value_i = 0; $column_value_i < $num_column_values; $column_value_i++) {
					$selection_string .= "\t" . $column_name;

					$value = $column_value[$column_value_i];

					if (is_null($value) || strtolower($value) == 'null') {
						$selection_string .= " IS NULL\n";
					} elseif (strtolower($value) == 'not null') {
						$selection_string .= " IS NOT NULL\n";
					} else {
						if (str_ends_with($value, '%')) {
							$selection_string .= ' LIKE ';
						} else {
							$selection_string .= ' = ';
						}

						$selection_string .= "'" . zen_db_input($value) . "'\n";
					}

					if ($column_value_i < ($num_column_values - 1)) {
						$selection_string .= " OR\n";
					}
				}

				$selection_string .= ')' . "\n";

			} else {
				$selection_string .= "\t" . $column_name;

				if (is_null($column_value) || strtolower($column_value) == 'null') {
					$selection_string .= " IS NULL\n";
				} elseif (strtolower($column_value) == 'not null') {
					$selection_string .= " IS NOT NULL\n";
				} else {
					if (str_ends_with($column_value, '%')) {
						$selection_string .= ' LIKE ';
					} else {
						$selection_string .= ' = ';
					}

					$selection_string .= "'" . zen_db_input($column_value) . "'\n";
				}
			}

			if ($column_name_i < ($num_selection_columns - 1)) {
				$selection_string .= " AND\n";
			}

			$column_name_i++;
		}

		$sql = "
			SELECT
				" . $columns_to_retrieve . "
			FROM
				" . TABLE_CEON_URI_MAPPINGS . "
			WHERE
				" . $selection_string;

		if (!is_null($order_by)) {
			$sql .= "\n" . ' ORDER BY ' . $order_by;
		}

		if (!is_null($limit)) {
			$sql .= "\n" . ' LIMIT ' . $limit;
		}

		if (!is_null($group_by)) {
			$sql .= "\n" . ' GROUP_BY ' . $group_by;
		}

		$sql .= ';';

		return $db->Execute($sql);
	}

	// }}}
}

// }}}
