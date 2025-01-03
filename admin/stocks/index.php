<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">المخزن</h3>
        <div class="card-tools">
			<a href="<?php echo base_url ?>admin/?page=purchase_order/manage_po" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>  فاتورة شراء جديدة</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
        <div class="container-fluid">
			<table data-page-length='100' class="table table-bordered table-stripped">
                    <colgroup>
                        <col width="5%">
                        <col width="20%">
                        <col width="10%">
                        <col width="40%">
						<col width="10%">
                        <col width="10%">
						<col width="20%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الصنف</th>
                            <th>المورد</th>
                            <th>الــوصف</th>
                            <th>المتاح</th>
							<th>سعر التكلفة</th>
							<th>اجمالى السعر</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
						$total_inventory = 0;
                        $qry = $conn->query("SELECT i.*,s.name as supplier FROM `item_list` i inner join supplier_list s on i.supplier_id = s.id order by `name` desc");
                        while($row = $qry->fetch_assoc()):
                            $in = $conn->query("SELECT SUM(quantity) as total FROM stock_list where item_id = '{$row['id']}' and type = 1")->fetch_array()['total'];
                            $out = $conn->query("SELECT SUM(quantity) as total FROM stock_list where item_id = '{$row['id']}' and type = 2")->fetch_array()['total'];
                            $row['available'] = $in - $out;
							$total_inventory += $row['available'] * $row['cost'] ;
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo $row['name'] ?></td>
                                <td><?php echo $row['supplier'] ?></td>
                                <td><?php echo $row['description'] ?></td>
                                <td class="text-right"><?php echo number_format($row['available']) ?></td>

								<td class="text-right"><?php echo number_format($row['cost']) ?></td>

                                <td class="text-right"><?php echo number_format($row['cost'] * $row['available'] ) ?></td>

							</tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
				<hr>
				<div class="col-md-6">
						<b>  حساب المخزن الكلى :</b>
						<span><?php echo number_format($total_inventory) ?></span>
			</div>
		</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('.delete_data').click(function(){
			_conf("هل انت متاكد من مسح مخزون المنتج بالكامل?","delete_receiving",[$(this).attr('data-id')])
		})
		$('.view_details').click(function(){
			uni_modal("Receiving Details","receiving/view_receiving.php?id="+$(this).attr('data-id'),'mid-large')
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('.table').dataTable();
	})
	function delete_receiving($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_receiving",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occured.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.reload();
				}else{
					alert_toast("An error occured.",'error');
					end_loader();
				}
			}
		})
	}
</script>