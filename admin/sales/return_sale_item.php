<?php
require_once('../../config.php');
$qry = $conn->query("SELECT * FROM sales_list r WHERE id = '{$_GET['id']}'");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_array() as $k => $v) {
            $$k = $v;
        }

?>
<style>

#uni_modal .modal-footer{
        display:none;
    }
    #confirm_modal{
        z-index: 9999;
    }

</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">فاتورة باسم - <?php echo isset($client) ? $client : '' ?> </h4>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <h4 class="text-info">منتجات الفاتورة</h4>
            <form id="return-form">
                <table class="table table-striped table-bordered" id="list">
                    <colgroup>
                        <col width="40%">
                        <col width="10%">
                        <col width="10%">
                        <col width="20%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr class="text-light bg-navy">
                            <th class="text-center py-1 px-2">المنتج</th>
                            <th class="text-center py-1 px-2">الكمية </th>
                            <th class="text-center py-1 px-2">السعر</th>
                            <th class="text-center py-1 px-2">الإجمالي</th>
                            <th class="text-center py-1 px-2">اجراء </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($stock_ids)) {
                            $stock_ids = trim($stock_ids, ',');
                            $qry = $conn->query("SELECT s.*, i.name, i.description, i.consumer_price 
                                                 FROM `stock_list` s 
                                                 INNER JOIN item_list i ON s.item_id = i.id 
                                                 WHERE s.id IN ({$stock_ids})");
                            if ($qry) {
                                while ($row = $qry->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td class="py-1 px-2">
                                            <?php echo htmlspecialchars($row['name']) ?> <br>
                                            <?php echo htmlspecialchars($row['description']) ?>
                                        </td>
                                        <td class="py-1 px-2 text-center">
                                            <?php echo ($row['quantity']); ?>
                                        </td>
                                       
                                        <td class="py-1 px-2 text-right">
                                            <?php echo number_format($row['consumer_price'], 2); ?>
                                        </td>
                                        <td class="py-1 px-2 text-right">
                                            <?php echo number_format($row['total'], 2); ?>
                                        </td>
                                        <td>

                                        <button type="button" id="submit-return-<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" onclick="returnItem('<?php echo $row['id']; ?>','<?php echo $row['item_id']; ?>', '<?php echo $row['quantity']; ?>', '<?php echo $row['total']; ?>')">ارجاع الى المخزن</button>
                                            </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "خطأ في الاستعلام: " . $conn->error;
                            }
                        } else {
                            echo "خطأ: لا توجد معرفات منتجات صالحة.";
                        }
                        ?>
                    </tbody>
                </table>

            </form>
        </div>
    </div>

    <button type="button"  style="width: 100px;margin: 15px;" class="btn btn-secondary" onclick="reload()">الغاء</button>

</div>
<?php
} else {
?>
<div>
    <h4>No Data</h4>
</div>
<?php
}
?>
<script>
    // $(function () {
 
    // });
    function returnItem(stockId,itemId,quantity,total_price) {
    // if (_conf("هل أنت متأكد من أنك تريد إرجاع هذا المنتج إلى المخزن؟")) {
        start_loader();

        $.ajax({
            url: _base_url_ + "classes/Master.php?f=return_sale_item_to_stock",
            method: 'POST',
            data: {
                itemId: itemId,
                quantity: quantity,
                total_price:total_price,
                stockId:stockId,
                sale_id: '<?php echo $_GET['id']; ?>'
            },
            success: function (response) {
                var jsonResponse = JSON.parse(response);
                if (jsonResponse.status == "success") {
                    alert_toast("تم الإرجاع إلى المخزن بنجاح.");
                    setTimeout(() => {
                        end_loader();
                        location.reload(); // إعادة تحميل الصفحة لتحديث البيانات
                    }, 200);
                } else {
                    alert_toast("حدث خطأ أثناء الإرجاع: " );
                    console.log(response)
                    end_loader();
                }
               
            },
            error: function (xhr, status, error) {
                alert_toast("An error occurred", 'error');
                end_loader();
                console.error(xhr, status, error);
            }
        });
    }

    function reload(){
        location.reload(); 
    }
// }



</script>
