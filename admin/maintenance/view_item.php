<?php require_once('./../../config.php') ?>
<?php 
 $qry = $conn->query("SELECT i.*,s.name as supplier FROM `item_list` i inner join supplier_list s on i.supplier_id = s.id where  i.id = '{$_GET['id']}' ");
 if($qry->num_rows > 0){
     foreach($qry->fetch_assoc() as $k => $v){
         $$k=$v;
     }
 }
?>
   <style>
    #uni_modal .modal-footer{
        display:none;
    }
</style> 
<div class="container-fluid" id="print_out">
    <div id='transaction-printable-details' class='position-relative'>
        <div class="row">
            <fieldset class="w-100">
                <div class="col-12">
                    
                    <dl>
                        <dt class="text-info">اسـم المنتج:</dt>
                        <dd class="pl-3"><?php echo $name ?></dd>
                        <dt class="text-info">الوصـف:</dt>
                        <dd class="pl-3"><?php echo isset($description) ? $description : '' ?></dd>
                        <dt class="text-info">السعر:</dt>
                        <dd class="pl-3"><?php echo isset($cost) ? number_format($cost,2) : '' ?></dd>
                        <dt class="text-info">سعر المستهلك:</dt>
                        <dd class="pl-3"><?php echo isset($consumer_price) ? number_format($consumer_price,2) : '' ?></dd>
                        <dt class="text-info">المورد:</dt>
                        <dd class="pl-3"><?php echo isset($supplier) ? $supplier : '' ?></dd>
                        <dt class="text-info">الحالة:</dt>
                        <dd class="pl-3">
                            <?php if($status == 1): ?>
                                <span class="badge badge-success rounded-pill">نـشط</span>
                            <?php else: ?>
                                <span class="badge badge-danger rounded-pill">غير نــشط</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </fieldset>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="col-12">
        <div class="d-flex justify-content-end align-items-center">
            <button class="btn btn-dark btn-flat" type="button" id="cancel" data-dismiss="modal">الغاء</button>
        </div>
    </div>
</div>
    

<script>
    $(function(){
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
    })
</script>