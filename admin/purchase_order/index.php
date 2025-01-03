<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">قائمة الفواتير</h3>
        <div class="card-tools">
			<a href="<?php echo base_url ?>admin/?page=purchase_order/manage_po" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>  اضافة فاتورة جديدة</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
        <div class="container-fluid">
			<table data-page-length='100' class="table table-bordered table-stripped">
                    <colgroup>
                        <col width="5%">
                        <col width="15%">
                        <col width="20%">
                        <col width="20%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تايخ الانشاء</th>
                            <th>الكود</th>
                            <th>المورد</th>
                            <th>المنتجات</th>
                            <th>الحالة</th>
                            <th>الاجراءت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $qry = $conn->query("SELECT p.*, s.name as supplier FROM `purchase_order_list` p INNER JOIN supplier_list s ON p.supplier_id = s.id ORDER BY p.`date_created` DESC");
                        if (!$qry) {
                            die("Error executing query: " . $conn->error);
                        }
                        while($row = $qry->fetch_assoc()):
                            $row['items'] = $conn->query("SELECT count(item_id) as `items` FROM `po_items` where po_id = '{$row['id']}' ")->fetch_assoc()['items'];
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo date("Y-m-d h:i A", strtotime($row['date_created'])); ?></td>
                                <td><?php echo $row['po_code'] ?></td>
                                <td><?php echo $row['supplier'] ?></td>
                                <td class="text-right"><?php echo number_format($row['items']) ?></td>
                                <td class="text-center">
                                    <?php if($row['status'] == 0): ?>
                                        <span class="badge badge-primary rounded-pill">تحت الانتظار</span>
                                    <?php elseif($row['status'] == 1): ?>
                                        <span class="badge badge-warning rounded-pill">مستلمة جزئيه</span>
                                        <?php elseif($row['status'] == 2): ?>
                                        <span class="badge badge-success rounded-pill">مستلمة</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger rounded-pill">غير معروف</span>
                                    <?php endif; ?>
                                </td>
                                <td align="center">
                                    <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                            اجراء
                                        <span class=""></span>
                                    </button>
                                    <div class="dropdown-menu" role="menu">

                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo base_url.'admin?page=purchase_order/view_po&id='.$row['id'] ?>" data-id="<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> عرض</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo base_url.'admin?page=purchase_order/manage_po&id='.$row['id'] ?>" data-id="<?php echo $row['id'] ?>"><span class="fa fa-edit text-primary"></span> تعديل</a>
                                        <!-- <div class="dropdown-divider"></div>
                                        <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> مسح</a> -->
                                    </div>
                                </td>
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
			_conf("هل انت متاكد من المسح?","delete_po",[$(this).attr('data-id')])
		})
		$('.view_details').click(function(){
			uni_modal("Payment Details","transaction/view_payment.php?id="+$(this).attr('data-id'),'mid-large')
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('.table').dataTable();
	})
	function delete_po($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_po",
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