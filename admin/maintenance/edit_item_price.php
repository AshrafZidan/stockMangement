<?php require_once('./../../config.php') ?>

<div class="card card-outline card-primary">
    <div class="card-body">
        <form action="" id="items-form-price">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_id" class="control-label">المورد</label>
                            <select name="supplier_id" id="supplier_id" class="custom-select select2">
                                <option <?php echo !isset($supplier_id) ? 'selected' : '' ?> disabled></option>
                                <?php 
                                $supplier = $conn->query("SELECT * FROM `supplier_list` where status = 1 order by `name` asc");
                                while($row=$supplier->fetch_assoc()):
                                ?>
                                <option value="<?php echo $row['id'] ?>" <?php echo isset($supplier_id) && $supplier_id == $row['id'] ? "selected" : "" ?> ><?php echo $row['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="" class="control-label">الحالة</label>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="increase" value="increase" checked>
                                <label class="form-check-label" for="increase">
                                    زيادة
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="decrease" value="decrease">
                                <label class="form-check-label" for="decrease">
                                    نقصان
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="" class="control-label">اختر النوع</label>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input value-type-radio" type="radio" name="adjustment_unit" id="percentage" value="percentage" checked>
                                <label class="form-check-label" for="percentage">
                                    نسبة مئوية (%)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input value-type-radio" type="radio" name="adjustment_unit" id="currency" value="currency">
                                <label class="form-check-label" for="currency">
                                    بالجنيه
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="value_input" id="value_label" class="control-label">النسبة المئوية (%)</label>
                        <input type="number" step="0.01" class="form-control" name="adjustment_value" id="value_input" placeholder="أدخل القيمة هنا">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(function(){
        $('.select2').select2({placeholder:"اختر من هنا",width:"relative"});

        // تغيير النص بناءً على الاختيار (نسبة أو جنيه)
        $('.value-type-radio').on('change', function() {
            const valueType = $(this).val();
            const label = valueType === 'percentage' ? 'النسبة المئوية (%)' : 'القيمة بالجنيه';
            $('#value_label').text(label);
            $('#value_input').attr('placeholder', 'أدخل ' + label);
        });

        $('#items-form-price').submit(function(e){
    e.preventDefault();  // لمنع إرسال النموذج بالطريقة التقليدية
    var _this = $(this);
    $('.err-msg').remove();  // إزالة الرسائل السابقة
    start_loader();  // بداية التحميل

    $.ajax({
        url: _base_url_ + "classes/Master.php?f=edit_items_supplier_price",  // مسار الملف
        data: new FormData($(this)[0]),  // إرسال البيانات عبر FormData
        cache: false,
        contentType: false,
        processData: false,
        method: 'POST',
        dataType: 'json',
        error: function(err) {
            console.log(err);
            alert_toast("An error occurred", 'error');
            end_loader();  // نهاية التحميل
        },
        success: function(resp) {
            if (typeof resp == 'object' && resp.status == 'success') {
                alert_toast(resp.msg, 'success'); // عرض الرسالة بنجاح
                setTimeout(() => {
                    
                    location.reload();  // إعادة تحميل الصفحة عند النجاح
                }, 500);
            } else if (resp.status == 'failed' && resp.msg) {
                var el = $('<div>')
                    el.addClass("alert alert-danger err-msg").text(resp.msg)
                    _this.prepend(el)
                    el.show('slow')
                    end_loader();  // نهاية التحميل
            } else {
                alert_toast("An error occurred", 'error');
                end_loader();  // نهاية التحميل
                console.log(resp);
            }
        }
    });
});


    });
</script>
