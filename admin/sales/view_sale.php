<?php 
$qry = $conn->query("SELECT * FROM sales_list r  where id = '{$_GET['id']}'");
if($qry->num_rows >0){
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v;
    }

?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">فاتورة رقم - <?php echo $sales_code ? $sales_code:'' ?></h4>
    </div>
    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label text-info">كود الفاتورة</label>
                    <div><?php echo isset($sales_code) ? $sales_code : '' ?></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="client" class="control-label text-info">اسم العميل</label>
                        <div><?php echo isset($client) ? $client : '' ?></div>
                    </div>
                </div>
            </div>
            <h4 class="text-info">المنتجات</h4>
            <table class="table table-striped table-bordered" id="list">
                <colgroup>
                    <col width="10%">
                    <col width="30%">
                    <col width="25%">
                    <col width="25%">
                </colgroup>
                <thead>
                    <tr class="text-light bg-navy">
                        <th class="text-center py-1 px-2">الكمية</th>
                        <th class="text-center py-1 px-2">المنتج</th>
                        <th class="text-center py-1 px-2">السعر</th>
                        <th class="text-center py-1 px-2">الاجمالى</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    // التحقق من أن $stock_ids يحتوي على قيم صالحة
                    if (!empty($stock_ids)) {
                        // تأكد من أن stock_ids مفصول بفواصل صحيحة
                        $stock_ids = trim($stock_ids, ','); // إزالة الفواصل الزائدة في البداية والنهاية

                        // استعلام للحصول على بيانات المنتجات
                        $qry = $conn->query("SELECT s.*, i.name, i.description, i.consumer_price 
                                            FROM `stock_list` s 
                                            INNER JOIN item_list i ON s.item_id = i.id 
                                            WHERE s.id IN ({$stock_ids})");

                        // التحقق من نجاح الاستعلام
                        if ($qry) {
                            $total = 0; // تأكد من تعريف المتغير $total قبل استخدامه
                            while ($row = $qry->fetch_assoc()) {
                                // جمع المجموع الإجمالي
                                $total += $row['total'];
                    ?>
                                <tr>

                                    <td class="py-1 px-2 text-center"><?php echo  ($row['quantity']) ?></td>
                                    <td class="py-1 px-2">
                                        <?php echo htmlspecialchars($row['name']) ?> <br>
                                        <?php echo htmlspecialchars($row['description']) ?>
                                    </td>
                                    <td class="py-1 px-2 text-right">
                                        <?php echo number_format($row['consumer_price'], 2) ?> <!-- تنسيق السعر إلى رقم عشري -->
                                    </td>
                                    <td class="py-1 px-2 text-right">
                                        <?php echo number_format($row['total'], 2) ?> <!-- تنسيق المجموع الإجمالي -->
                                    </td>
                                </tr>
                    <?php
                            }
                        } else {
                            // إذا فشل الاستعلام، يمكننا إظهار رسالة خطأ أو تسجيلها
                            echo "خطأ في الاستعلام: " . $conn->error;
                            // أو يمكن تسجيل الخطأ في سجل الأخطاء
                            error_log("Query failed: " . $conn->error);
                        }
                    } else {
                        echo "خطأ: لا توجد معرفات منتجات صالحة.";
                    }
                    ?>

                    
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="3">الاجمالى</th>
                        <th class="text-right py-1 px-2 grand-total"><?php echo isset($amount) ? number_format($amount,2) : 0 ?></th>
                    </tr>

                    <tr>
                            <th class="text-right py-1 px-2" colspan="3">
                                المدفوع
                            </th>
                            <th class="text-right py-1 px-2 paid"><?php echo isset($paid) ? number_format($paid,2) : 0 ?>
                            </th>
                        </tr>

                        <tr>
                        <th class="text-right py-1 px-2" colspan="3">
                            المتبقى
                        </th>
                        <th class="text-right py-1 px-2"><?php echo isset($remaining) ? number_format($remaining,2) : 0 ?>

                        </th>
                    </tr>

                </tfoot>
            </table>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="remarks" class="text-info control-label">الملاحظات</label>
                        <p><?php echo isset($remarks) ? $remarks : '' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-success" type="button" id="print">طباعة</button>
        <a class="btn btn-flat btn-primary" href="<?php echo base_url.'/admin?page=sales/manage_sale&id='.(isset($id) ? $id : '') ?>">تعديل</a>
        <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin?page=sales' ?>">العودة للقائمة</a>
    </div>
</div>
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 text-center qty">
            <span class="visible"></span>
            <input type="hidden" name="item_id[]">
            <input type="hidden" name="unit[]">
            <input type="hidden" name="qty[]">
            <input type="hidden" name="consumer_price[]">
            <input type="hidden" name="total[]">
        </td>
        <td class="py-1 px-2 text-center unit">
        </td>
        <td class="py-1 px-2 item">
        </td>
        <td class="py-1 px-2 text-right cost">
        </td>
        <td class="py-1 px-2 text-right total">
        </td>
    </tr>
</table>
<?php
}else{
?>
<div>
    <h4>No Data</h4></div>
    <?php
}
?>
<script>
    
    $(function(){
        $('#print').click(function(){
            start_loader()
            var _el = $('<div>')
            var _head = $('head').clone()
                _head.find('title').text("Sales Record - Print View")
            var p = $('#print_out').clone()
            p.find('tr.text-light').removeClass("text-light bg-navy")
            _el.append(_head)
            _el.append('<div class="d-flex justify-content-center">'+
                      '<div class="col-1 text-right">'+
                      '<img src="<?php echo validate_image($_settings->info('logo')) ?>" width="65px" height="65px" />'+
                      '</div>'+
                      '<div class="col-10">'+
                      '<h4 class="text-center"><?php echo $_settings->info('name') ?></h4>'+
                      '<h4 class="text-center">Sales Record</h4>'+
                      '</div>'+
                      '<div class="col-1 text-right">'+
                      '</div>'+
                      '</div><hr/>')
            _el.append(p.html())
            var nw = window.open("","","width=1200,height=900,left=250,location=no,titlebar=yes")
                     nw.document.write(_el.html())
                     nw.document.close()
                     setTimeout(() => {
                         nw.print()
                         setTimeout(() => {
                            nw.close()
                            end_loader()
                         }, 200);
                     }, 500);
        })
    })
</script>