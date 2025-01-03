<?php 
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM sales_list where id = '{$_GET['id']}'");
    if($qry->num_rows >0){
        foreach($qry->fetch_array() as $k => $v){
            $$k = $v;
        }
    }
}
?>
<style>
    select[readonly].select2-hidden-accessible + .select2-container {
        pointer-events: none;
        touch-action: none;
        background: #eee;
        box-shadow: none;
    }

    select[readonly].select2-hidden-accessible + .select2-container .select2-selection {
        background: #eee;
        box-shadow: none;
    }
</style>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title"><?php echo isset($id) ? "تفاصيل الفاتورة - ".$sales_code : 'فاتورة جديدة' ?></h4>
    </div>

    <div class="card-body">
        <form action="" id="sale-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <label class="control-label text-info">الكود</label>
                        <input type="text" class="form-control form-control-sm rounded-0" value="<?php echo isset($sales_code) ? $sales_code : '' ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="client" class="control-label text-info">اسم العميل</label>
                            <input type="text" name="client" class="form-control form-control-sm rounded-0" value="<?php echo isset($client) ? $client : 'Guest' ?>" >
                        </div>
                    </div>
                </div>
                <hr>
                <fieldset>
                    <legend class="text-info">تفاصيل المنتج</legend>
                    <div class="row justify-content-center align-items-end">
                            <?php 
                                $item_arr = array();
                                $cost_arr = array();
                                $item = $conn->query("SELECT i.*, s.name AS supplier_name FROM `item_list` i 
                                INNER JOIN `supplier_list` s ON i.supplier_id = s.id 
                                WHERE i.status = 1 
                                ORDER BY i.name ASC");
                                while($row=$item->fetch_assoc()):
                                    $item_arr[$row['id']] = $row;
                                    $cost_arr[$row['id']] = $row['consumer_price'];
                                endwhile;
                            ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_id" class="control-label">المنتج</label>
                                <select  id="item_id" class="custom-select select2">
                                    <option disabled selected></option>
                                    <?php foreach($item_arr as $k =>$v): ?>
                                        <option value="<?php echo $k ?>"> <?php echo $v['name']?> - <?php echo $v['supplier_name']?>  </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                      
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="qty" class="control-label">الكمية</label>
                                <input type="number" step="any" class="form-control rounded-0" id="qty">
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="form-group">
                                <button type="button" style="width:60%" class="btn btn-flat  btn-primary" id="add_to_list">اضافة منتج</button>
                            </div>
                        </div>
                </fieldset>
                <hr>
                <table class="table table-striped table-bordered" id="list">
                    <colgroup>

                        <col width="5%">
                        <col width="40%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr class="text-light bg-navy">
                            <th class="text-center py-1 px-2"></th>
                            <th class="text-center py-1 px-2">المنتج</th>
                            <th class="text-center py-1 px-2">الكمية</th>
                            <th class="text-center py-1 px-2">التكلفة</th>
                            <th class="text-center py-1 px-2">الاجمالى</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        if (!empty($stock_ids)):
                        if(isset($id)):
                        $qry = $conn->query("SELECT s.*,i.name,i.description FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
                        while($row = $qry->fetch_assoc()):
                            $total += $row['total']
                        ?>
                        <tr data-id="<?php echo $row['item_id']; ?>">
                            
                            <td class="py-1 px-2 text-center">
                                <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
                            </td>

                            <td class="py-1 px-2 item">
                            <?php echo $row['name']; ?> <br>
                            <?php echo $row['description']; ?>
                            </td>

                            <td class="py-1 px-2 text-center qty">
                            <i onclick="increseQty(this)" class="add-icon fa fa-plus"></i>
                                <span class="visible"> <?php echo  $row['quantity'] ?></span>
                                <i onclick="decreseQty(this)" class="add-icon fa fa-minus"></i>

                                <input type="hidden" name="item_id[]" value="<?php echo $row['item_id']; ?>">
                                <input type="hidden" name="qty[]" value="<?php echo  $row['quantity'] ; ?>">
                                <input type="hidden" name="old_qty[]" value="<?php echo $row['quantity']; ?>">
                                <input type="hidden" name="price[]" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="total[]" value="<?php echo $row['total']; ?>">
                                <input type="hidden" name="stock_id_id[]" value="<?php echo $row['id']; ?>">
                            </td>
                         
                         
                            <td class="py-1 px-2 text-right cost">
                            <?php echo number_format($row['price']); ?>
                            </td>
                            <td class="py-1 px-2 text-right total">
                            <?php echo number_format($row['total']); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="4">
                                الاجمالى
                                <input type="hidden" name="amount" value="<?php echo isset($discount) ? $discount : 0 ?>">
                            </th>
                            <th class="text-right py-1 px-2 grand-total">0</th>
                        </tr>

                       
                        <tr>
                            <th class="text-right py-1 px-2" colspan="4">
                                المدفوع
                                <input type="hidden" name="paid" value="<?php echo isset($paid) ? $paid : 0 ?>">
                            </th>
                            <th class="text-right py-1 px-2 paid">
                            <input  name="paid" id="paid_input"  class='' value="<?php echo isset($paid) ? $paid : 0 ?>" type="number" min="0">
                            </th>
                        </tr>

                        <tr>
                        <th class="text-right py-1 px-2" colspan="4">
                            المتبقى
                            <input type="hidden" name="remaining" value="<?php echo isset($remaining) ? $remaining : 0 ?>">
                        </th>
                        <th class="text-right py-1 px-2 remaing">
                            <?php echo isset($remaining) ? $remaining : 0; ?>
                        </th>
                    </tr>

                    </tfoot>
                </table>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remarks" class="text-info control-label">ملاحظات</label>
                            <textarea name="remarks" id="remarks" rows="3" class="form-control rounded-0"><?php echo isset($remarks) ? $remarks : '' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-primary" type="submit" form="sale-form">حفظ</button>
        <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin?page=sales' ?>">الغاء</a>
    </div>
</div>
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 item">
        </td>
        <td class="py-1 px-2 text-center qty">
            <i onclick="increseQty(this)" class="add-icon fa fa-plus"></i>
            <span class="visible"></span>
            <i onclick="decreseQty(this)" class="add-icon fa fa-minus"></i>
            <input type="hidden" name="item_id[]">
            <input type="hidden" name="qty[]">
            <input type="hidden" name="price[]">
            <input type="hidden" name="total[]">
            <?php if(isset($_GET['id'])): ?>
            <input type="hidden" name="old_qty[]" value="0">
            <!-- <input type="hidden" name="stock_id_id[]" value=""> -->
        <?php endif; ?>

        </td>
       
      
        <td class="py-1 px-2 text-right cost">
        </td>
        <td class="py-1 px-2 text-right total">
        </td>
    </tr>
</table>
<script>
    var items = JSON.parse('<?php echo addslashes(json_encode($item_arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>');
    var costs = $.parseJSON('<?php echo json_encode($cost_arr) ?>')
    var editPage = <?php echo isset($_GET['id']) ? 'true' : 'false'; ?>;
    var removedQty = [];
    var removedTotal =[];
    var removedItemId = [];
    var removedPrice = [];
    $(function(){
        $('.select2').select2({
            placeholder:"اختر منتج",
            width:'resolve',
        })
        $('#add_to_list').click(function(){
            var item = $('#item_id').val()
            var qty = $('#qty').val() > 0 ? $('#qty').val() : 0;
            var price = costs[item] || 0
            var total = parseFloat(qty) *parseFloat(price)
            // console.log(supplier,item)
            var item_name = items[item].name || 'N/A';
            var item_description = items[item].description || 'N/A';
            var tr = $('#clone_list tr').clone()
            if(item == '' || qty == ''){
                alert_toast('تاكد من كل البيانات.','warning');
                return false;
            }
            if($('table#list tbody').find('tr[data-id="'+item+'"]').length > 0){
                alert_toast('المنتج موجود بالفعل.','error');
                return false;
            }
            tr.find('[name="item_id[]"]').val(item)
            tr.find('[name="qty[]"]').val(qty)
            tr.find('[name="price[]"]').val(price)
            tr.find('[name="total[]"]').val(total)
            tr.attr('data-id',item)
            tr.find('.qty .visible').text(qty)
            tr.find('.item').html(item_name+'<br/>'+item_description)
            tr.find('.cost').text(parseFloat(price).toLocaleString('en-US'))
            tr.find('.total').text(parseFloat(total).toLocaleString('en-US'))
            $('table#list tbody').append(tr)
            calc();
            calcPaid();
            $('#item_id').val('').trigger('change')
            $('#qty').val('')
            tr.find('.rem_row').click(function(){
                rem($(this))
            })
            
            $('[name="discount_perc"],[name="tax_perc"]').on('input',function(){
                calc();
                calcPaid();
            })
            $('#supplier_id').attr('readonly','readonly')
        })
        $('#sale-form').submit(function(e){
			e.preventDefault();
            var _this = $(this)
			 $('.err-msg').remove();
			start_loader();
			$.ajax({
				url:_base_url_+"classes/Master.php?f=save_sale",
				data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
				error:err=>{
					console.log(err)
					alert_toast("An error occured",'error');
					end_loader();
				},
				success:function(resp){
					if(resp.status == 'success'){
						location.replace(_base_url_+"admin/?page=sales");
					}else if(resp.status == 'failed' && !!resp.msg){
                        var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            end_loader()
                    }else{
						alert_toast("An error occured",'error');
						end_loader();
                        console.log(resp)
					}
                    $('html,body').animate({scrollTop:0},'fast')
				}
			})
		})

        if('<?php echo isset($id) && $id > 0 ?>' == 1){
            calc();
            calcPaid();
            $('#supplier_id').trigger('change')
            $('#supplier_id').attr('readonly','readonly')
            $('table#list tbody tr .rem_row').click(function(){
                rem($(this))
            })
        }
        $("#paid_input").blur(function() {
            calcPaid();
        })

      
    })
    function calcPaid(){
            var grandTotalText = $(".grand-total").text();
            var grandTotalNumber = parseFloat(grandTotalText.replace(/,/g, ''));
            var remaing = $(".remaing").text(0);
            var input =  $("#paid_input");
            if ( input.val() > grandTotalNumber ||  input.val() < 0 ) {
                input.val(0);
                alert_toast("المدفوع يجب ان يكون اقل من الاجمالى.",'error');
            }else{
                var remaingNumber= grandTotalNumber - input.val();
                var remaing =  $(".remaing").text(parseFloat(remaingNumber).toLocaleString('en-US',{style:'decimal',maximumFractionDigit:2}))   
                $('[name="remaining"]').val(parseFloat(remaingNumber))
            }
        }
    function rem(_this){
        
        if (editPage) {
            addHiddenInputToRemoveAll(_this);
        }
       
        _this.closest('tr').remove()
        calc();
        calcPaid();
        if($('table#list tbody tr').length <= 0)
            $('#supplier_id').removeAttr('readonly')

    }
    function calc(){
        var grand_total = 0;
        $('table#list tbody input[name="total[]"]').each(function(){
            grand_total += parseFloat($(this).val())
            
        })
       
        $('table#list tfoot .grand-total').text(parseFloat(grand_total).toLocaleString('en-US',{style:'decimal',maximumFractionDigit:2}))
        $('[name="amount"]').val(parseFloat(grand_total))

    }
 
    function addHiddenInputToRemoveAll(_this) {
    removedQty.push($(_this.closest('tr').find('td')[2]) // Wrap the second <td> in a jQuery object
        .find('input[name="old_qty[]"]').val());
    removedItemId.push(
        $(_this.closest('tr').find('td')[2]) // Wrap the second <td> in a jQuery object
            .find('input[name="item_id[]"]').val()
    );
    removedPrice.push(
        $(_this.closest('tr').find('td')[2]) // Wrap the second <td> in a jQuery object
            .find('input[name="price[]"]').val()
    );
    removedTotal.push(
        $(_this.closest('tr').find('td')[2]) // Wrap the second <td> in a jQuery object
            .find('input[name="total[]"]').val()
    );

    // Update or create hidden inputs
    updateOrCreateHiddenInput('removed_total[]', removedTotal.join(','));
    updateOrCreateHiddenInput('removed_qty[]', removedQty.join(','));
    updateOrCreateHiddenInput('removed_price[]', removedPrice.join(','));
    updateOrCreateHiddenInput('removed_item_id[]', removedItemId.join(','));
}

function updateOrCreateHiddenInput(inputName, value) {
    let inputElement = document.querySelector(`input[name="${inputName}"]`);
    if (inputElement) {
        inputElement.value = value;
    } else {
        let newInput = document.createElement('input');
        newInput.type = 'hidden';
        newInput.name = inputName;
        newInput.value = value;
        $('#sale-form').append(newInput);
    }
}

    function increseQty(_this) {
    const qtyElement = $(_this).siblings().closest('.visible')

    let currentQtyVal = parseInt(qtyElement.text(), 10);
    if (isNaN(currentQtyVal)) {
        currentQtyVal = 0;
    }
    currentQtyVal += 1;
    qtyElement.text(currentQtyVal);
    var itemId = $(qtyElement).closest('tr').find('[name="item_id[]"]').val();    ;
    var price = costs[itemId] || 0;
    var total = parseFloat(currentQtyVal) *parseFloat(price);
    var tr = $('#list tr[data-id="'+itemId+'"]');
    tr.find('[name="qty[]"]').val(currentQtyVal);
    tr.find('.total').text(parseFloat(total).toLocaleString('en-US'));
    tr.find('[name="total[]"]').val(total)
    calc();
    calcPaid();
    }
    function decreseQty(_this){
    const qtyElement = $(_this).siblings().closest('.visible')

    let currentQtyVal = parseInt(qtyElement.text(), 10);
    if (isNaN(currentQtyVal)) {
        currentQtyVal = 0;
    }
    if (currentQtyVal > 1) {
        currentQtyVal -= 1;
        qtyElement.text(currentQtyVal);
    }
    var itemId = $(qtyElement).closest('tr').find('[name="item_id[]"]').val();
    ;

    var price = costs[itemId] || 0;
    var total = parseFloat(currentQtyVal) *parseFloat(price);
    var tr = $('#list tr[data-id="'+itemId+'"]');
    tr.find('[name="qty[]"]').val(currentQtyVal);
    tr.find('.total').text(parseFloat(total).toLocaleString('en-US'));
    tr.find('[name="total[]"]').val(total); 
    calc();
    calcPaid();
    }
</script>