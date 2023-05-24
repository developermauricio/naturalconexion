<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Formatter_Storage_Summary_Session implements WOE_Formatter_Storage {
    const SUMMARY_PRODUCTS_KEY = 'woe_summary_products';
    const SUMMARY_CUSTOMERS_KEY = 'woe_summary_customers';

    private $summaryProducts = false;
    private $summaryCustomers = false;
    private $summaryKey;
    private $rows_already_sorted = false;

	/**
	 * @var array<int, WOE_Formatter_Storage_Column>
	 */
	protected $header;

    public function __construct($summaryKey)
    {
		$this->summaryKey = $summaryKey;
        if ($this->summaryKey == self::SUMMARY_PRODUCTS_KEY) {
            $this->summaryProducts = true;
        } else if ($this->summaryKey == self::SUMMARY_CUSTOMERS_KEY) {
            $this->summaryCustomers = true;
        }
        self::checkCreateSession();
    }

    private static function checkCreateSession() {
		if ( ! session_id() ) {
			@session_start();
		}
	}

    public function load() {
        if (!isset($_SESSION[$this->summaryKey . '_header'])) {
            return;
        }
        $header = $_SESSION[$this->summaryKey . '_header'];
        $this->header = array();

        foreach ($header as $item) {
            $column = new WOE_Formatter_Storage_Column();
            $column->setKey($item['key']);
            $column->setMeta($item['meta']);
            $this->header[] = $column;
        }
    }

    public function getColumns()
    {
        return $this->header;
    }

    public function insertColumn( $column ) {
		if ( $column instanceof WOE_Formatter_Storage_Column ) {
			$this->header[] = $column;
		}
	}

    public function saveHeader()
    {
        $rawHeader = array();
        foreach($this->header as $column) {
            $rawHeader[] = array('key' => $column->getKey(), 'meta' => $column->getMeta());
        }
        $_SESSION[$this->summaryKey . '_header'] = $rawHeader;
    }

    public function close() {}

    public function delete() {}

    public function initRowIterator() {
		if( !$this->rows_already_sorted )
			$this->sortByName();
		do_action('woe_summary_before_output');
		if(isset($_SESSION[$this->summaryKey]))
			reset($_SESSION[$this->summaryKey]);
    }

    public function getNextRow() {
		if( !isset($_SESSION[$this->summaryKey]) ) {
			return false;
		}
			
        $row = current($_SESSION[$this->summaryKey]);
		if ($row === false) { //all rows were returned
			unset($_SESSION[$this->summaryKey . '_header']);
			unset($_SESSION[$this->summaryKey]);
			return $row;
		}

		$meta = $row['woe_internal_meta'];
		unset($row['woe_internal_meta']);

        $rowObj = new WOE_Formatter_Storage_Row();
        $rowObj->setKey(key($_SESSION[$this->summaryKey]));
        $rowObj->setMeta($meta);
        $rowObj->setData($row);

		next($_SESSION[$this->summaryKey]);

        return $rowObj;
    }

	/**
	 * @return WOE_Formatter_Storage_Row
	 */
	public function getRow($key) {
		if(!isset($_SESSION[$this->summaryKey][$key])) {
			return null;
		}

		$row = $_SESSION[$this->summaryKey][$key];

		$meta = $row['woe_internal_meta'];
		unset($row['woe_internal_meta']);

        $rowObj = new WOE_Formatter_Storage_Row();
        $rowObj->setKey($key);
        $rowObj->setMeta($meta);
        $rowObj->setData($row);

        return $rowObj;
	}

	/**
	 * @param WOE_Formatter_Storage_Row $rowObj
	 */
	public function setRow($rowObj) {
		$key = $rowObj->getKey();
		$row = $rowObj->getData();
		$row['woe_internal_meta'] = $rowObj->getMeta();
		$_SESSION[$this->summaryKey][$key] = $row;
	}

	public function processDataForPreview($rows) {
		$this->sortByName();

		do_action( 'woe_summary_before_output' );

		foreach ($_SESSION[$this->summaryKey] as $row) {
			unset($row['woe_internal_meta']);
			$rows[] = $row;
		}
		// reset non-numerical indexes -- 0 will be bold in preview
		$rows = array_values($rows);

		unset($_SESSION[$this->summaryKey . '_header']);
		unset($_SESSION[$this->summaryKey]);

		return $rows;
	}

	public function insertRowAndSave($row)  {}

    private function sortByName()
    {
        if (isset($_SESSION[$this->summaryKey . '_header'])) {
            $first_row = array_column($_SESSION[$this->summaryKey . '_header'], 'key');
	    $possible_sort_columns = array("name","product_name","product_name_main");
	    foreach($possible_sort_columns as $key) {
                if ( !in_array($key, $first_row) ) continue;
                uasort($_SESSION[$this->summaryKey], function ($a, $b) use($key) {
                    return strcasecmp($a[$key], $b[$key]);
                });
                break;
            }
        }
    }

    /**
     * @return bool
     */
    public function isSummaryProducts() {
        return $this->summaryProducts;
    }

    /**
     * @return bool
     */
    public function isSummaryCustomers() {
        return $this->summaryCustomers;
    }
    
	public function sortRowsByColumn($sort) {
		uasort($_SESSION[$this->summaryKey], function($a,$b) use($sort){
			$field      = !is_array($sort) ? $sort : (isset($sort[0]) ? str_replace("plain_products_","",$sort[0]) : '');
			$direction  = !is_array($sort) ? 'asc' : (isset($sort[1]) ?  strtolower($sort[1]) : 'asc');
			$type       = !is_array($sort) ? 'string' : (isset($sort[2]) ? $sort[2] : 'string');
			if ($type === 'money' || $type === 'number') {
				return $direction === 'asc' ? $a[$field] - $b[$field] : $b[$field] - [$field];
			}

			if ($type === 'date') {
				return $direction === 'asc' ? strtotime($a[$field]) - strtotime($b[$field]) : strtotime($b[$field]) - strtotime($a[$field]);
			}

			return $direction === 'asc' ? strcmp($a[$field],$b[$field]) : (-1) * strcmp($a[$field],$b[$field]);
		} );
		$this->rows_already_sorted = true;
	}
    
}