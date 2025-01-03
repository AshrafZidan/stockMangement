<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">الفواتير المتأخرة</h3>
	</div>
	<div class="card-body">
		<div class="container-fluid">
        <div class="container-fluid">
			<table data-page-length='100' class="table table-bordered table-stripped">
                    <colgroup>
                        <col width="5%">
                        <col width="15%">
                        <col width="15%">
                        <col width="15%">
                        <col width="10%">
                        <col width="10%">
                        <col width="5%">
                        <col width="5%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تاريخ الانشاء</th>
                            <th>تاريخ اخر تعديل</th>
                            <th>اسم العميل</th>
                            <th>المنتجات</th>
                            <th> السعر</th>
                            <th> المدفوع </th>
                            <th> المتبقى </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $qry = $conn->query("SELECT * FROM `sales_list` WHERE `remaining` > 0 ORDER BY `date_updated` DESC");
                        while($row = $qry->fetch_assoc()):
                            $row['items'] = count(explode(',',$row['stock_ids']));
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo date("Y-m-d H:i A",strtotime($row['date_created'])) ?></td>
                                <td><?php echo date("Y-m-d H:i A",strtotime($row['date_updated'])) ?></td>

                                <td><?php echo $row['client'] ?></td>
                                <td class="text-right"><?php echo number_format($row['items']) ?></td>
                                <td class="text-right"><?php echo number_format($row['amount'],2) ?></td>
                                <td class="text-right"><?php echo number_format($row['paid'],2) ?></td>
                                <td class="text-right"><?php echo number_format($row['remaining'],2) ?></td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
		</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('.delete_data').click(function(){
			_conf("هل انت متأكد من مسح الفاتورة?","delete_sale",[$(this).attr('data-id')])
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('.table').dataTable();
	})
	function delete_sale($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_sale",
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