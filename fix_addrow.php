<?php
$file = 'c:/xampp8.2/htdocs/transport/app/Views/admin/add_inhouse_vw.php';
$content = file_get_contents($file);

// Find the template literal and replace it entirelty
$old_pattern = '/var newRow = \$\(`\s*<tr id="row\$\{i\}">.*?<\/tr>\s*`\);/s';
$new_template = 'var newRow = $(`
            <tr id="row${i}">
                <td>${i}</td>
                <td>
                    <select class="form-control type-select" name="itemUseAs[]">
                        <option value="1">Service</option>
                        <option value="2">Product</option>
                    </select>
                </td>
                <td>
                    <select name="items[]" class="form-control items">${optionsHtml}</select>
                </td>
                <td>
                    <small class="availableqty text-muted d-block mt-1">Available: 0 | Unit Price: 0.00</small>
                </td>
                <td><input type="number" name="qty[]" class="form-control qty" placeholder="Enter quantity" min="0.01" step="any"/></td>
                <td><input type="text" name="price[]" class="form-control price" readonly/></td>
                <td><select name="mechanic_name[]" class="form-control mechanic-select">${mechOptHtml}</select></td>
                <td><input type="text" name="totalprice" placeholder="Total Price" class="form-control tprice" readonly /></td>
                <td><button type="button" class="btn btn-danger btn_remove">X</button></td>
            </tr>
        `);';

$new_content = preg_replace($old_pattern, $new_template, $content);

if ($new_content !== $content) {
    file_put_contents($file, $new_content);
    echo "Successfully updated addRow template.";
} else {
    echo "Could not find the addRow template pattern.";
}
?>
