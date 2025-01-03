<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">المنتجات</h3>
		<div class="card-tools">
			<a href="javascript:void(0)" id="create_new" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>  اضافة جـديد</a>
			<a href="javascript:void(0)" id="edit_item_price" class="btn  btn-outline-secondary"><span class="fas fa-dollar-sign"></span>  تعديل اسعار منتجات </a>


			
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
        <div class="container-fluid">
			<table  data-page-length='100' class="table table-hovered table-striped">
				<colgroup>
					<col width="5%">
					<col width="10%">
					<col width="30%">
					<col width="30%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="20%">
				</colgroup>
				<thead>
					<tr>
						<th>#</th>
						<th>تاريخ الانـشاء</th>
						<th>الاسم</th>
						<th>الـمورد</th>
						<th>السعر</th>
						<th>سعر المستهلك</th>
						<th>الـحالة</th>
						<th>الاجراءت</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$i = 1;
						$qry = $conn->query("SELECT i.*,s.name as supplier from `item_list` i inner join supplier_list s on i.supplier_id = s.id order by i.name asc,s.name asc ");
						while($row = $qry->fetch_assoc()):
					?>
						<tr>
							<td class="text-center"><?php echo $i++; ?></td>
							<td><?php echo date("Y-m-d H:i",strtotime($row['date_created'])) ?></td>
							<td><?php echo $row['name'] ?></td>
							<td><?php echo $row['supplier'] ?></td>
							<td><?php echo $row['cost'] ?></td>
							<td><?php echo $row['consumer_price'] ?></td>
							<td class="">
                                <?php if($row['status'] == 1): ?>
                                    <span class="badge badge-success rounded-pill">نـشط</span>
                                <?php else: ?>
                                    <span class="badge badge-danger rounded-pill">غـير نـشط</span>
                                <?php endif; ?>
                            </td>
							<td >
								 <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
				                  		الاجراء
				                    <span class="sr-only"></span>
				                  </button>
				                  <div class="dropdown-menu" role="menu">
				                    <a class="dropdown-item view_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> عـرض</a>
				                    <div class="dropdown-divider"></div>
				                    <a class="dropdown-item edit_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-edit text-primary"></span> تعديل</a>
				                    <div class="dropdown-divider"></div>
				                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> مسح</a>
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
			_conf("هل انت متاكد من حذف المنتج?","delete_category",[$(this).attr('data-id')])
		})
		$('#create_new').click(function(){
			uni_modal("<i class='fa fa-plus'></i> اضافة منتج جديد","maintenance/manage_item.php","mid-large")
		})
		$('.edit_data').click(function(){
			uni_modal("<i class='fa fa-edit'></i> تعديل تفاصيل المنتج","maintenance/manage_item.php?id="+$(this).attr('data-id'),"mid-large")
		})
		$('.view_data').click(function(){
			uni_modal("<i class='fa fa-box'></i> تفاصيل المنتج","maintenance/view_item.php?id="+$(this).attr('data-id'),"")
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('#edit_item_price').click(function(){
			uni_modal("<i class='fa fa-dollar-sign'></i> تعديل اسعار منتجات  ","maintenance/edit_item_price.php","mid-large")
		})
		$('.table').dataTable();
	})
	function delete_category($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_item",
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