<?php


class Installment extends ObjectModel
{
	public $id_installment;

	/** @var integer */
	public $id_shop;

	/** @var integer Currency id which country belongs */
	public $id_payment_product;

	/** @var integer */
	public $qty;

	/** @var float */
	public $rate;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'installment',
		'primary' => 'id_installment',
		'multilang' => false,
		'fields' => array(
      'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
      'id_payment_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),  
      'qty' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
      'rate' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),  
		),
		'associations' => array(
			'id_store' => array('type' => self::HAS_ONE),
		)
	);

	public function delete()
	{
		if (!parent::delete())
			return false;
		return Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'installment WHERE id_installment = '.(int)$this->id);
	}


	public static function getInstallments($id_shop, $id_payment_product=null, $qty=null, $rate=null)
	{
    $installments = array();
		$query = ' SELECT * ';
    $query .= ' FROM `'._DB_PREFIX_.'installment` ';
    $query .= " WHERE id_shop = '{$id_shop}' ";
    if($id_payment_product)
      $query .= " AND id_payment_product = '{$id_payment_product}' ";
    if($qty)
      $query .= " AND qty = '{$qty}' ";    
    if($rate)
      $query .= " AND rate = '{$rate}' ";      
    $query .= " ORDER BY qty ASC";
      
    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
    
		foreach ($result as $row)
			$installments[] = $row;

		return $installments;
	}
  
	public static function getInstallment($id_shop, $id_payment_product, $qty)
	{
		$query = ' SELECT * ';
    $query .= ' FROM `'._DB_PREFIX_.'installment` ';
    $query .= " WHERE id_shop = '{$id_shop}' ";
    $query .= " AND id_payment_product = '{$id_payment_product}' ";
    $query .= " AND qty = '{$qty}' ";    
    $query .= ' LIMIT 1 ';  
    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
    
		return $result[0];
	}  
  
	public static function getInstallmentsByIdShop($id_shop)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'installment`
		WHERE `id_shop` = '.(int)$id_shop);
	}
  
	public static function getEnabledProducts($id_shop)
	{
    $products = array();
		$query = ' SELECT * ';
    $query .= ' FROM `'._DB_PREFIX_.'installment` ';
    $query .= " WHERE id_shop = '{$id_shop}' ";
    $query .= " GROUP BY id_payment_product";
      
    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
    
		foreach ($result as $row)
			$products[] = $row['id_payment_product'];

		return $products;
	}    
  
}