<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_supplier(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$check = $this->conn->query("SELECT * FROM `supplier_list` where `name` = '{$name}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "supplier Name already exist.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `supplier_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `supplier_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			if(empty($id)){
				$res['msg'] = "New Supplier successfully saved.";
				$id = $this->conn->insert_id;
			}else{
				$res['msg'] = "Supplier successfully updated.";
			}
		$this->settings->set_flashdata('success',$res['msg']);
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_supplier(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `supplier_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Supplier successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_item(){
		extract($_POST);
		$data = "";
	
		// Validate that consumer_price is greater than or equal to cost
		if (isset($consumer_price) && isset($cost) && $consumer_price < $cost) {
			$resp['status'] = 'failed';
			$resp['msg'] = "Consumer price must be equal to or greater than the cost.";
			return json_encode($resp);
			exit;
		}
	
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id'))){
				$v = $this->conn->real_escape_string($v);
				if(!empty($data)) $data .= ",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
	
		$check = $this->conn->query("SELECT * FROM `item_list` where `name` = '{$name}' and `supplier_id` = '{$supplier_id}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
	
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Item already exists under selected supplier.";
			return json_encode($resp);
			exit;
		}
	
		if(empty($id)){
			$sql = "INSERT INTO `item_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `item_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
	
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$this->settings->set_flashdata('success',"New Item successfully saved.");
			else
				$this->settings->set_flashdata('success',"Item successfully updated.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
	
		return json_encode($resp);
	}
	
	function delete_item(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `item_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Item  successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}

	function save_po(){
		if(empty($_POST['id'])){
			$prefix = "PO";
			$code = sprintf("%'.04d",1);
			while(true){
				$check_code = $this->conn->query("SELECT * FROM `purchase_order_list` where po_code ='".$prefix.'-'.$code."' ")->num_rows;
				if($check_code > 0){
					$code = sprintf("%'.04d",$code+1);
				}else{
					break;
				}
			}
			$_POST['po_code'] = $prefix."-".$code;
		}
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id')) && !is_array($_POST[$k])){
				if(!is_numeric($v))
				$v= $this->conn->real_escape_string($v);
				if(!empty($data)) $data .=", ";
				$data .=" `{$k}` = '{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `purchase_order_list` set {$data}";
		}else{
			$sql = "UPDATE `purchase_order_list` set {$data} where id = '{$id}'";
		}
		$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$po_id = $this->conn->insert_id;
			else
				$po_id = $id;
			$resp['id'] = $po_id;
			$data = "";
			foreach($item_id as $k =>$v){
				if(!empty($data)) $data .=", ";
				$data .= "('{$po_id}','{$v}','{$qty[$k]}','{$price[$k]}','{$unit[$k]}','{$total[$k]}')";
			}
			if(!empty($data)){
				$this->conn->query("DELETE FROM `po_items` where po_id = '{$po_id}'");
				$save = $this->conn->query("INSERT INTO `po_items` (`po_id`,`item_id`,`quantity`,`price`,`unit`,`total`) VALUES {$data}");
				if(!$save){
					$resp['status'] = 'failed';
					if(empty($id)){
						$this->conn->query("DELETE FROM `purchase_order_list` where id '{$po_id}'");
					}
					$resp['msg'] = 'PO has failed to save. Error: '.$this->conn->error;
					$resp['sql'] = "INSERT INTO `po_items` (`po_id`,`item_id`,`quantity`,`price`,`unit`,`total`) VALUES {$data}";
				} else {
					// Update stock for each item
					$stock_ids= array();

					foreach($item_id as $k =>$v){
						if(!empty($data)) $data .=", ";
						if (empty($id)) {
							$sql = "INSERT INTO stock_list (`item_id`,`quantity`,`price`,`unit`,`total`,`type`) VALUES ('{$v}','{$qty[$k]}','{$price[$k]}','{$unit[$k]}','{$total[$k]}','1')";
							$this->conn->query($sql);
							$stock_ids[] = $this->conn->insert_id;
							}
							else{

							// Check if the item exists in the `stock_list`
							$checkItemSql = "SELECT * FROM stock_list WHERE item_id = '{$v}' AND type = 1 LIMIT 1";
							$checkItem = $this->conn->query($checkItemSql);

							if ($checkItem && $checkItem->num_rows > 0) {
									$quantity_diff = $qty[$k] - $old_qty[$k];
									if ($quantity_diff > 0) {
										$sql = "UPDATE stock_list 
												SET quantity = quantity + {$quantity_diff} 
												WHERE item_id = '{$v}' AND type = 1 
												LIMIT 1";
										$this->conn->query($sql);
									}
							
							}
							else
							{
								$sql = "INSERT INTO stock_list (`item_id`, `quantity`, `price`, `unit`, `total`, `type`) 
										VALUES ('{$v}', '{$qty[$k]}', '{$price[$k]}', '{$unit[$k]}', '{$total[$k]}', '1')";
								$this->conn->query($sql);
								$stock_ids[] = $this->conn->insert_id;
							}
		
							}
						
						// if($qty[$k] < $oqty[$k]){
						// 	$bo_ids[] = $k;
						// }
					}
				}
			}
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = 'An error occurred. Error: '.$this->conn->error;
		}
		if($resp['status'] == 'success'){
			if(empty($id)){
				$this->settings->set_flashdata('success'," تم اضافة فاتورة جديدة بنجاح.");
			}else{
				$this->settings->set_flashdata('success'," تم تعديل الفاتورة بنجاح.");
			}
		}
	
		return json_encode($resp);
	}
	
	function delete_po(){
		extract($_POST);
		$bo = $this->conn->query("SELECT * FROM back_order_list where po_id = '{$id}'");
		$del = $this->conn->query("DELETE FROM `purchase_order_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"po's Details Successfully deleted.");
			if($bo->num_rows > 0){
				$bo_res = $bo->fetch_all(MYSQLI_ASSOC);
				$r_ids = array_column($bo_res, 'receiving_id');
				$bo_ids = array_column($bo_res, 'id');
			}
			$qry = $this->conn->query("SELECT * FROM receiving_list where (form_id='{$id}' and from_order = '1') ".(isset($r_ids) && count($r_ids) > 0 ? "OR id in (".(implode(',',$r_ids)).") OR (form_id in (".(implode(',',$bo_ids)).") and from_order = '2') " : "" )." ");
			while($row = $qry->fetch_assoc()){

				// $this->conn->query("DELETE FROM `stock_list` where id in ({$row['stock_ids']}) ");
				// echo "DELETE FROM `stock_list` where id in ({$row['stock_ids']}) </br>";
				$sql = "UPDATE  stock_list SET quantity=quantity - {$qty[$k]} where item_id = {$v} And type = 1";
				$this->conn->query($sql);								

			}
			$this->conn->query("DELETE FROM receiving_list where (form_id='{$id}' and from_order = '1') ".(isset($r_ids) && count($r_ids) > 0 ? "OR id in (".(implode(',',$r_ids)).") OR (form_id in (".(implode(',',$bo_ids)).") and from_order = '2') " : "" )." ");
			// echo "DELETE FROM receiving_list where (form_id='{$id}' and from_order = '1') ".(isset($r_ids) && count($r_ids) > 0 ? "OR id in (".(implode(',',$r_ids)).") OR (form_id in (".(implode(',',$bo_ids)).") and from_order = '2') " : "" )."  </br>";
			// exit;
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_receiving(){
		if(empty($_POST['id'])){
			$prefix = "BO";
			$code = sprintf("%'.04d",1);
			while(true){
				$check_code = $this->conn->query("SELECT * FROM `back_order_list` where bo_code ='".$prefix.'-'.$code."' ")->num_rows;
				if($check_code > 0){
					$code = sprintf("%'.04d",$code+1);
				}else{
					break;
				}
			}
			$_POST['bo_code'] = $prefix."-".$code;
		}else{
			$get = $this->conn->query("SELECT * FROM back_order_list where receiving_id = '{$_POST['id']}' ");
			if($get->num_rows > 0){
				$res = $get->fetch_array();
				$bo_id = $res['id'];
				$_POST['bo_code'] = $res['bo_code'];	
			}else{

				$prefix = "BO";
				$code = sprintf("%'.04d",1);
				while(true){
					$check_code = $this->conn->query("SELECT * FROM `back_order_list` where bo_code ='".$prefix.'-'.$code."' ")->num_rows;
					if($check_code > 0){
						$code = sprintf("%'.04d",$code+1);
					}else{
						break;
					}
				}
				$_POST['bo_code'] = $prefix."-".$code;

			}
		}
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','bo_code','supplier_id','po_id')) && !is_array($_POST[$k])){
				if(!is_numeric($v))
				$v= $this->conn->real_escape_string($v);
				if(!empty($data)) $data .=", ";
				$data .=" `{$k}` = '{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `receiving_list` set {$data}";
		}else{
			$sql = "UPDATE `receiving_list` set {$data} where id = '{$id}'";
		}
		$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
			$r_id = $this->conn->insert_id;
			else
			$r_id = $id;
			$resp['id'] = $r_id;
			if(!empty($id)){
				$stock_ids = $this->conn->query("SELECT stock_ids FROM `receiving_list` where id = '{$id}'")->fetch_array()['stock_ids'];
				$this->conn->query("DELETE FROM `stock_list` where id in ({$stock_ids})");
			}
			$stock_ids= array();
			foreach($item_id as $k =>$v){
				if(!empty($data)) $data .=", ";
				$sql = "INSERT INTO stock_list (`item_id`,`quantity`,`price`,`unit`,`total`,`type`) VALUES ('{$v}','{$qty[$k]}','{$price[$k]}','{$unit[$k]}','{$total[$k]}','1')";
				$this->conn->query($sql);
				$stock_ids[] = $this->conn->insert_id;
				if($qty[$k] < $oqty[$k]){
					$bo_ids[] = $k;
				}
			}
			if(count($stock_ids) > 0){
				$stock_ids = implode(',',$stock_ids);
				$this->conn->query("UPDATE `receiving_list` set stock_ids = '{$stock_ids}' where id = '{$r_id}'");
			}
			if(isset($bo_ids)){
				$this->conn->query("UPDATE `purchase_order_list` set status = 1 where id = '{$po_id}'");
				if($from_order == 2){
					$this->conn->query("UPDATE `back_order_list` set status = 1 where id = '{$form_id}'");
				}
				if(!isset($bo_id)){
					$sql = "INSERT INTO `back_order_list` set 
							bo_code = '{$bo_code}',	
							receiving_id = '{$r_id}',	
							po_id = '{$po_id}',	
							supplier_id = '{$supplier_id}',	
							discount_perc = '{$discount_perc}',	
							tax_perc = '{$tax_perc}'
						";
				}else{
					$sql = "UPDATE `back_order_list` set 
							receiving_id = '{$r_id}',	
							po_id = '{$form_id}',	
							supplier_id = '{$supplier_id}',	
							discount_perc = '{$discount_perc}',	
							tax_perc = '{$tax_perc}',
							where bo_id = '{$bo_id}'
						";
				}
				$bo_save = $this->conn->query($sql);
				if(!isset($bo_id))
				$bo_id = $this->conn->insert_id;
				$stotal =0; 
				$data = "";
				foreach($item_id as $k =>$v){
					if(!in_array($k,$bo_ids))
						continue;
					$total = ($oqty[$k] - $qty[$k]) * $price[$k];
					$stotal += $total;
					if(!empty($data)) $data.= ", ";
					$data .= " ('{$bo_id}','{$v}','".($oqty[$k] - $qty[$k])."','{$price[$k]}','{$unit[$k]}','{$total}') ";
				}
				$this->conn->query("DELETE FROM `bo_items` where bo_id='{$bo_id}'");
				$save_bo_items = $this->conn->query("INSERT INTO `bo_items` (`bo_id`,`item_id`,`quantity`,`price`,`unit`,`total`) VALUES {$data}");
				if($save_bo_items){
					$discount = $stotal * ($discount_perc /100);
					$stotal -= $discount;
					$tax = $stotal * ($tax_perc /100);
					$stotal += $tax;
					$amount = $stotal;
					$this->conn->query("UPDATE back_order_list set amount = '{$amount}', discount='{$discount}', tax = '{$tax}' where id = '{$bo_id}'");
				}

			}else{
				$this->conn->query("UPDATE `purchase_order_list` set status = 2 where id = '{$po_id}'");
				if($from_order == 2){
					$this->conn->query("UPDATE `back_order_list` set status = 2 where id = '{$form_id}'");
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = 'An error occured. Error: '.$this->conn->error;
		}
		if($resp['status'] == 'success'){
			if(empty($id)){
				$this->settings->set_flashdata('success'," New Stock was Successfully received.");
			}else{
				$this->settings->set_flashdata('success'," Received Stock's Details Successfully updated.");
			}
		}

		return json_encode($resp);
	}
	function delete_receiving(){
		extract($_POST);
		$qry = $this->conn->query("SELECT * from  receiving_list where id='{$id}' ");
		if($qry->num_rows > 0){
			$res = $qry->fetch_array();
			$ids = $res['stock_ids'];
		}
		if(isset($ids) && !empty($ids))
		$this->conn->query("DELETE FROM stock_list where id in ($ids) ");
		$del = $this->conn->query("DELETE FROM receiving_list where id='{$id}' ");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Received Order's Details Successfully deleted.");

			if(isset($res)){
				if($res['from_order'] == 1){
					$this->conn->query("UPDATE purchase_order_list set status = 0 where id = '{$res['form_id']}' ");
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function delete_bo(){
		extract($_POST);
		$bo =$this->conn->query("SELECT * FROM `back_order_list` where id = '{$id}'");
		if($bo->num_rows >0)
		$bo_res = $bo->fetch_array();
		$del = $this->conn->query("DELETE FROM `back_order_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"po's Details Successfully deleted.");
			$qry = $this->conn->query("SELECT `stock_ids` from  receiving_list where form_id='{$id}' and from_order = '2' ");
			if($qry->num_rows > 0){
				$res = $qry->fetch_array();
				$ids = $res['stock_ids'];
				$this->conn->query("DELETE FROM stock_list where id in ($ids) ");

				$this->conn->query("DELETE FROM receiving_list where form_id='{$id}' and from_order = '2' ");
			}
			if(isset($bo_res)){
				$check = $this->conn->query("SELECT * FROM `receiving_list` where from_order = 1 and form_id = '{$bo_res['po_id']}' ");
				if($check->num_rows > 0){
					$this->conn->query("UPDATE `purchase_order_list` set status = 1 where id = '{$bo_res['po_id']}' ");
				}else{
					$this->conn->query("UPDATE `purchase_order_list` set status = 0 where id = '{$bo_res['po_id']}' ");
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_return() {
		if (empty($_POST['id'])) {
			$prefix = "R";
			$code = sprintf("%'.04d", 1);
			while (true) {
				$check_code = $this->conn->query("SELECT * FROM `return_list` WHERE return_code ='" . $prefix . '-' . $code . "' ")->num_rows;
				if ($check_code > 0) {
					$code = sprintf("%'.04d", $code + 1);
				} else {
					break;
				}
			}
			$_POST['return_code'] = $prefix . "-" . $code;
		}
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_array($_POST[$k])) {
				if (!is_numeric($v)) {
					$v = $this->conn->real_escape_string($v);
				}
				if (!empty($data)) $data .= ", ";
				$data .= " `{$k}` = '{$v}' ";
			}
		}
		if (empty($id)) {
			$sql = "INSERT INTO `return_list` SET {$data}";
		} else {
			$sql = "UPDATE `return_list` SET {$data} WHERE id = '{$id}'";
		}
		$save = $this->conn->query($sql);
		if ($save) {
			$resp['status'] = 'success';
			if (empty($id)) {
				$return_id = $this->conn->insert_id;
			} else {
				$return_id = $id;
			}
			$resp['id'] = $return_id;
			$data = "";
			$sids = array();
			$get = $this->conn->query("SELECT * FROM `return_list` WHERE id = '{$return_id}'");
			if ($get->num_rows > 0) {
				$res = $get->fetch_array();
				if (!empty($res['stock_ids'])) {
					$this->conn->query("DELETE FROM `stock_list` WHERE id IN ({$res['stock_ids']})");
				}
			}
			foreach ($item_id as $k => $v) {
				// Validate stock quantity
				$stock_check = $this->conn->query("SELECT `quantity` FROM `stock_list` WHERE item_id = '{$v}'");
				if ($stock_check->num_rows > 0) {
					$stock = $stock_check->fetch_assoc();
					if ($qty[$k] > $stock['quantity']) {
						$resp['status'] = 'failed';
						$resp['msg'] = "   المرتجع اكبر من الكمية الموجودة فالمخزن  للمنتج او خطأ فى نوع الوحدة{$v}.";
						return json_encode($resp);
					}
				} else {
					$resp['status'] = 'failed';
					$resp['msg'] = " لايوجد فى المخزن من المنتج {$v}.";
					return json_encode($resp);
				}
	
				// Insert return into stock_list
				$sql = "INSERT INTO `stock_list` SET item_id='{$v}', `quantity` = '{$qty[$k]}', `unit` = '{$unit[$k]}', `price` = '{$price[$k]}', `total` = '{$total[$k]}', `type` = 2 ";
				$save = $this->conn->query($sql);
				if ($save) {
					$sids[] = $this->conn->insert_id;
				}
			}
			$sids = implode(',', $sids);
			$this->conn->query("UPDATE `return_list` SET stock_ids = '{$sids}' WHERE id = '{$return_id}'");
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = 'An error occurred. Error: ' . $this->conn->error;
		}
		if ($resp['status'] == 'success') {
			if (empty($id)) {
				$this->settings->set_flashdata('success', "New Returned Item Record was Successfully created.");
			} else {
				$this->settings->set_flashdata('success', "Returned Item Record's Successfully updated.");
			}
		}
	
		return json_encode($resp);
	}
	
	function delete_return(){
		extract($_POST);
		$get = $this->conn->query("SELECT * FROM return_list where id = '{$id}'");
		if($get->num_rows > 0){
			$res = $get->fetch_array();
		}
		$del = $this->conn->query("DELETE FROM `return_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Returned Item Record's Successfully deleted.");
			if(isset($res)){
				$this->conn->query("DELETE FROM `stock_list` where id in ({$res['stock_ids']})");
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}


	function save_sale() {
		// Check if we are adding a new sale or updating an existing one
		if (empty($_POST['id'])) {
			// If no 'id' is present, call the function for adding a new sale
			return $this->save_sale_add();
		} else {
			// If 'id' is present, call the function for updating an existing sale
			return $this->save_sale_edit();
		}
	}

	function save_sale_add() {
		// Generate a sales code if not provided
		if (empty($_POST['id'])) {
			$prefix = "SALE";
			$code = sprintf("%'.04d", 1);
			while (true) {
				$check_code = $this->conn->query("SELECT * FROM `sales_list` WHERE sales_code = '".$prefix.'-'.$code."'")->num_rows;
				if ($check_code > 0) {
					$code = sprintf("%'.04d", $code + 1);
				} else {
					break;
				}
			}
			$_POST['sales_code'] = $prefix . "-" . $code;
		}
	
		extract($_POST);
		$data = "";
	
		// Validate stock availability for all items
		foreach ($item_id as $k => $v) {
			if (empty($v)) {
				die("Error: Missing item ID for one of the entries.");
			}
	
			// Calculate total available stock
			$in_query = $this->conn->query("SELECT SUM(quantity) as total FROM stock_list WHERE item_id = '{$v}' AND type = '1'");
			$out_query = $this->conn->query("SELECT SUM(quantity) as total FROM stock_list WHERE item_id = '{$v}' AND type = '2'");
	
			if (!$in_query || !$out_query) {
				die("Error in stock query: " . $this->conn->error);
			}
	
			$in_quantity = $in_query->fetch_assoc()['total'] ?? 0;
			$out_quantity = $out_query->fetch_assoc()['total'] ?? 0;
			$available_quantity = $in_quantity - $out_quantity;
	
			// Fetch item name for error messages
			$item_query = $this->conn->query("SELECT name FROM `item_list` WHERE id = '{$v}'");
			if (!$item_query) {
				die("Error in item query: " . $this->conn->error);
			}
			$item_name = $item_query->fetch_assoc()['name'] ?? "Unknown Item";
	
			// Check if requested quantity exceeds available stock
			if ($qty[$k] > $available_quantity) {
				$resp['status'] = 'failed';
				$resp['msg'] = "الكميه من المنتج '{$item_name}' اكبر من الموجود بالمخزن. المتاح: {$available_quantity}, المطلوب: {$qty[$k]}";
				return json_encode($resp);
			}
		}
	
		// Prepare sales list data
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_array($_POST[$k])) {
				$v = $this->conn->real_escape_string($v);
				$data .= (!empty($data) ? ", " : "") . "`{$k}` = '{$v}'";
			}
		}
	
		// Insert new sale into sales_list table
		$sql = "INSERT INTO `sales_list` SET {$data}";
		$save = $this->conn->query($sql);
	
		if ($save) {
			$resp['status'] = 'success';
			$sale_id = $this->conn->insert_id;
			$resp['id'] = $sale_id;
	
			// Process stock deduction
			$sids = array();
			foreach ($item_id as $k => $v) {
				// Deduct stock (Ensure this only affects rows with sufficient stock)
				$deduct = $this->conn->query("UPDATE `stock_list` SET quantity = quantity  WHERE item_id = '{$v}' AND quantity >= {$qty[$k]} LIMIT 1");
	
				if (!$deduct) {
					$resp['status'] = 'failed';
					$resp['msg'] = "Error in stock deduction: " . $this->conn->error;
					return json_encode($resp);
				}
	
				// Log the sale in stock_list (type = 2 for sales)
				$sql = "INSERT INTO `stock_list` (item_id, quantity, price, total, type) 
						VALUES ('{$v}', {$qty[$k]}, '{$price[$k]}', '{$total[$k]}', 2)";
				$save = $this->conn->query($sql);
	
				if ($save) {
					$sids[] = $this->conn->insert_id;
				} else {
					$resp['status'] = 'failed';
					$resp['msg'] = "Error in stock log: " . $this->conn->error;
					return json_encode($resp);
				}
			}
	
			// Update sales record with stock IDs
			$sids = implode(',', $sids);
			$this->conn->query("UPDATE `sales_list` SET stock_ids = '{$sids}' WHERE id = '{$sale_id}'");
	
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = 'An error occurred: ' . $this->conn->error;
		}
	
		// Flash message on success
		if ($resp['status'] == 'success') {
			$msg = "تم اضافة فاتورة جديدة بنجاح.";
			$this->settings->set_flashdata('success', $msg);
		}
	
		return json_encode($resp);
	}

	

	function save_sale_edit() {
		extract($_POST);
		$resp = array();
	
		if (empty($id)) {
			$resp['status'] = 'failed';
			$resp['msg'] = 'Sale ID is missing.';
			return json_encode($resp);
		}
	
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'removed_qty', 'removed_item_id', 'item_id', 'qty', 'old_qty', 'price', 'total', 'stock_id_id')) && !is_array($v)) {
				$v = $this->conn->real_escape_string($v);
				$data .= (!empty($data) ? ", " : "") . "`{$k}` = '{$v}'";
			}
		}
	
		if (!empty($data)) {
			$sql = "UPDATE `sales_list` SET {$data} WHERE id = '{$id}'";
			$update = $this->conn->query($sql);
	
			if (!$update) {
				$resp['status'] = 'failed';
				$resp['msg'] = "Error updating sales_list: " . $this->conn->error;
				return json_encode($resp);
			}
		}
	
		if (isset($removed_qty) && !empty($removed_qty)) {
			if (!is_array($removed_qty)) {
				$removed_qty = explode(',', $removed_qty);
			}
	
			if (!is_array($removed_item_id)) {
				$removed_item_id = explode(',', $removed_item_id);
			}
	
			foreach ($removed_qty as $k => $v) {
				$item_ids = explode(',', $removed_item_id[$k]);
				$quantities = explode(',', $v);
	
				foreach ($item_ids as $index => $item_id) {
					$removed_quantity = $quantities[$index];
	
					$sql_get_exist_stock = "SELECT * FROM `stock_list` WHERE item_id = '{$item_id}' AND type = '2'";
					$get_exist_stock = $this->conn->query($sql_get_exist_stock);
	
					if ($get_exist_stock && $get_exist_stock->num_rows > 0) {
						$row = $get_exist_stock->fetch_assoc();
						$stock_id = $row['id'];
	
						$sql_delete_stock = "DELETE FROM `stock_list` WHERE item_id = '{$item_id}' AND quantity = '{$removed_quantity}' AND type = '2'";
						$delete_stock = $this->conn->query($sql_delete_stock);
	
						if ($delete_stock) {
							$sql = "UPDATE `sales_list` 
									SET stock_ids = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', stock_ids, ','), ',{$stock_id},', ',')) 
									WHERE id = '{$id}' AND FIND_IN_SET('{$stock_id}', stock_ids) > 0";
							$result = $this->conn->query($sql);
	
							if ($this->conn->error) {
								$resp['status'] = 'failed';
								$resp['msg'] = "Error updating sales_list: " . $this->conn->error;
								return json_encode($resp);
							}
						} else {
							$resp['status'] = 'failed';
							$resp['msg'] = "Error deleting stock for item_id: {$item_id}";
							return json_encode($resp);
						}
					} else {
						$resp['status'] = 'failed';
						$resp['msg'] = "No stock found with type '2' for item_id: {$item_id}";
						return json_encode($resp);
					}
				}
			}
		}
	
		if (isset($item_id) && isset($qty) && isset($old_qty)) {
			if (!is_array($item_id)) {
				$item_id = explode(',', $item_id);
			}
			
			$sids = [];
			foreach ($item_id as $k => $v) {

				if (!isset($qty[$k], $old_qty[$k])) {
					continue;
				}
		
						
				$in_query = $this->conn->query("SELECT SUM(quantity) as total FROM stock_list WHERE item_id = '{$v}' AND type = '1'");
				$out_query = $this->conn->query("SELECT SUM(quantity) as total FROM stock_list WHERE item_id = '{$v}' AND type = '2'");
		
				if (!$in_query || !$out_query) {
					$resp['status'] = 'failed';
					$resp['msg'] = "Error fetching stock for item {$v}: " . $this->conn->error;
					return json_encode($resp);
				}
		
				$in_quantity = $in_query->fetch_assoc()['total'] ?? 0;
				$out_quantity = $out_query->fetch_assoc()['total'] ?? 0;
				$available_quantity = $in_quantity - $out_quantity;
		

				$quantity_diff = $qty[$k] - $old_qty[$k];
		
				// if no change in item
				if ($quantity_diff == 0) {
					$query = "SELECT id FROM `stock_list` WHERE item_id = '{$v}' AND type = 2  AND  `quantity` =  $qty[$k]" ;
					$result = $this->conn->query($query);
					// print($result->num_rows );
					if ($result && $result->num_rows > 0) {
						$row = $result->fetch_assoc();
						$sids[] = $row['id'];
					}
					continue;
				}

				else if ($quantity_diff > 0) {

					if ($quantity_diff > $available_quantity) {
						$resp['status'] = 'failed';
						$resp['msg'] = "الكميه من المنتج '{$v}' اكبر من المخزن. المتاح: {$available_quantity}, المطلوب: {$quantity_diff}";
						return json_encode($resp);
					}
		
					if (isset($stock_id_id[$k])) {
						// print($quantity_diff)
						$sql = "UPDATE `stock_list` 
								SET quantity = quantity + {$quantity_diff}, 
									price = '{$price[$k]}', 
									total = '{$total[$k]}'
								WHERE item_id = '{$v}' AND id = {$stock_id_id[$k]} AND type = 2 LIMIT 1";
						$save = $this->conn->query($sql);
		
						if (!$save) {
							$resp['status'] = 'failed';
							$resp['msg'] = "Error updating stock log for item '{$v}': " . $this->conn->error;
							return json_encode($resp);
						}else{
						    // Add the ID to the sids array
   							 $sids[] = $stock_id_id[$k];
	
						}
					} else {
						$sql = "INSERT INTO `stock_list` (item_id, quantity, price, total, type) 
								VALUES ('{$v}', {$qty[$k]}, '{$price[$k]}', '{$total[$k]}', 2)";
						$save = $this->conn->query($sql);
		
						if ($save) {
							$sids[] = $this->conn->insert_id;
						} else {
							$resp['status'] = 'failed';
							$resp['msg'] = "Error inserting new stock for item '{$v}': " . $this->conn->error;
							return json_encode($resp);
						}
					}
				}
				
				else if ($quantity_diff < 0) {
					$quantity_to_return = abs($quantity_diff);
		
					$sql = "UPDATE `stock_list`
							SET quantity = quantity - {$quantity_to_return}, 
								price = '{$price[$k]}', 
								total = '{$total[$k]}'
							WHERE item_id = '{$v}' AND id = {$stock_id_id[$k]} AND type = 2 LIMIT 1";
					$save = $this->conn->query($sql);
		
					if (!$save) {
						$resp['status'] = 'failed';
						$resp['msg'] = "Error updating return stock log for item '{$v}': " . $this->conn->error;
						return json_encode($resp);
					}else{
						  // Add the ID to the sids array
						  $sids[] = $stock_id_id[$k];
					}
				}
			}
		
			if (!empty($sids)) {
				$sids = implode(',', $sids);
				$this->conn->query("UPDATE `sales_list` SET stock_ids = '{$sids}' WHERE id = '{$id}'");
			}
		}
		
		$resp['status'] = 'success';
		return json_encode($resp);
	}
	
	
	function delete_sale(){
		extract($_POST);
		$get = $this->conn->query("SELECT * FROM sales_list where id = '{$id}'");
		if($get->num_rows > 0){
			$res = $get->fetch_array();
		}
		$del = $this->conn->query("DELETE FROM `sales_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Sales Record's Successfully deleted.");
			if(isset($res)){
				$this->conn->query("DELETE FROM `stock_list` where id in ({$res['stock_ids']})");
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}






	function return_sale_item_to_stock(){
	
		$itemId = isset($_POST['itemId']) ? $_POST['itemId'] : null;
		$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : null;
		$stockId = isset($_POST['stockId']) ? $_POST['stockId'] : null;
		$saleId = isset($_POST['sale_id']) ? $_POST['sale_id'] : null;
		$total_price = isset($_POST['total_price']) ? $_POST['total_price'] : null;

	
		
		// Validate that all required data is present
		if (empty($itemId) || empty($quantity) || empty($stockId) || empty($saleId) || empty($total_price)) {
			$resp['status'] = 'failed';
			$resp['error'] = "خطأ: بيانات ناقصة.";
			return json_encode($resp);
		}
		
		



				$sql_get_exist_stock = "SELECT * FROM stock_list WHERE item_id = '{$itemId}' AND id = '{$stockId}' AND quantity = '{$quantity}' AND type = '2'";
				$get_exist_stock = $this->conn->query($sql_get_exist_stock);

				if ($get_exist_stock && $get_exist_stock->num_rows > 0) {
					$row = $get_exist_stock->fetch_assoc();
					$stock_id = $row['id'];

					$sql_delete_stock = "DELETE FROM stock_list WHERE item_id = '{$itemId}' AND id = '{$stockId}' AND quantity = '{$quantity}' AND type = '2'";
					$delete_stock = $this->conn->query($sql_delete_stock);

					$sql_select_sale = "SELECT amount, remaining , paid FROM `sales_list` WHERE id = '{$saleId}'";
					$select_sale = $this->conn->query($sql_select_sale);
					
					if ($delete_stock) {

						if ($select_sale && $select_sale->num_rows > 0) {
							$sale_data = $select_sale->fetch_assoc();
							$current_amount = $sale_data['amount'];
							$current_remaining = $sale_data['remaining'];
							$paid = $sale_data['paid'];
						} else {
							// Handle error: sale not found or query failed
							$resp['status'] = 'failed';
							$resp['msg'] = "Sale not found.";
							return json_encode($resp);
						}
											
						$new_amount = $current_amount - $total_price;
						$new_remaining = $new_amount - $paid; // Calculate remaining balance


						$sql = "UPDATE `sales_list` 
								SET stock_ids = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', stock_ids, ','), ',{$stockId},', ',')) ,
								amount = $new_amount ,
		                        remaining = {$new_remaining}
								WHERE id = '{$saleId}' AND FIND_IN_SET('{$stockId}', stock_ids) > 0";
						$result = $this->conn->query($sql);

						if ($this->conn->error) {
							$resp['status'] = 'failed';
							$resp['msg'] = "Error updating sales_list: " . $this->conn->error;
							return json_encode($resp);
						}else{

						
							$resp['status'] = 'success';
							$resp['msg'] = 'success';

							return json_encode($resp); 
						}
					} else {
						$resp['status'] = 'failed';
						$resp['msg'] = "Error deleting stock for item_id: {$item_id}";
						return json_encode($resp);
					}
				} else {
					$resp['status'] = 'failed';
					$resp['msg'] = "No stock found with type '2' for item_id: {$item_id}";
					return json_encode($resp);
				}
				
				// $resp['status'] = 'failed';
				// $resp['msg'] = 'An error occurred:  {$item_id}';
	
			// }


	}



	














	function edit_items_supplier_price() {
		extract($_POST); // استخراج القيم من POST
	
		$supplier_id = isset($supplier_id) ? $this->conn->real_escape_string($supplier_id) : null;
		$adjustment_type = isset($adjustment_type) ? $this->conn->real_escape_string($adjustment_type) : null; // زيادة أو نقصان
		$adjustment_value = isset($adjustment_value) ? $this->conn->real_escape_string($adjustment_value) : null; // النسبة أو القيمة
		$adjustment_unit = isset($adjustment_unit) ? $this->conn->real_escape_string($adjustment_unit) : null; // النسبة % أو الجنيه
	
		if (!$supplier_id || !$adjustment_type || !$adjustment_value || !$adjustment_unit) {
			$resp['status'] = 'failed';
			$resp['msg'] = "All inputs are required. {$supplier_id} --  {$adjustment_type}  -- {$adjustment_value} -- {$adjustment_unit}";
			return json_encode($resp);
			exit;
		}
	
	
		if (!is_numeric($adjustment_value) || $adjustment_value <= 0) {
			$resp['status'] = 'failed';
			$resp['msg'] = "القيمه يجب ان تكون اكبر من 0.";
			return json_encode($resp);
			exit;
		}
	
		// Fetch all products for the selected supplier
		$qry = $this->conn->query("SELECT * FROM `item_list` WHERE `supplier_id` = '{$supplier_id}'");
		if ($qry->num_rows == 0) {
			$resp['status'] = 'failed';
			$resp['msg'] = "لا توجد منتجات لهذا المورد ";
			return json_encode($resp);
			exit;
		}
	
		// تحديد العملية (زيادة أو نقصان)
		$operation = $adjustment_type == 'increase' ? '+' : '-';
	
		// تحديد نوع التعديل (نسبة مئوية أو قيمة)
		if ($adjustment_unit == 'percentage') {
			// تعديل بالنسبة المئوية
			$sql = "UPDATE `item_list` 
					SET `cost` = `cost` * (1 {$operation} ({$adjustment_value} / 100)), 
						`consumer_price` = `consumer_price` * (1 {$operation} ({$adjustment_value} / 100)) 
					WHERE `supplier_id` = '{$supplier_id}'";
		} else {
			// تعديل بالجنيه
			$sql = "UPDATE `item_list` 
					SET `cost` = `cost` {$operation} {$adjustment_value}, 
						`consumer_price` = `consumer_price` {$operation} {$adjustment_value} 
					WHERE `supplier_id` = '{$supplier_id}'";
		}
		
	
		// تنفيذ التعديل
		$update = $this->conn->query($sql);
	
		if ($update) {
			$resp['status'] = 'success';
			$resp['msg'] = "تم التعديل بنجاح.";
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = "فشل التعديل: " . $this->conn->error;
		}
	
		return json_encode($resp);
	}


}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_supplier':
		echo $Master->save_supplier();
	break;
	case 'edit_items_supplier_price':
		echo $Master->edit_items_supplier_price();
	break;
	case 'delete_supplier':
		echo $Master->delete_supplier();
	break;
	case 'save_item':
		echo $Master->save_item();
	break;
	case 'delete_item':
		echo $Master->delete_item();
	break;
	case 'get_item':
		echo $Master->get_item();
	break;
	case 'save_po':
		echo $Master->save_po();
	break;
	case 'delete_po':
		echo $Master->delete_po();
	break;
	case 'save_receiving':
		echo $Master->save_receiving();
	break;
	case 'delete_receiving':
		echo $Master->delete_receiving();
	break;
	case 'save_return':
		echo $Master->save_return();
	break;
	case 'delete_return':
		echo $Master->delete_return();
	break;
	case 'save_sale':
		echo $Master->save_sale();
	break;
	case 'delete_sale':
		echo $Master->delete_sale();
	break;
	case 'return_sale_item_to_stock':
		echo $Master->return_sale_item_to_stock();
	break;
	
	default:
		// echo $sysset->index();
		break;
}