<?php include("header.php"); ?>

<div class="page-body-wrapper" style="background:#f4f7f6;">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="font-weight-bold"><i class="fa fa-file-text-o text-warning"></i> Journal Voucher</h3>
                    </div>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="voucherForm" action="<?= base_url('admin/saveVoucher'); ?>" method="post">
                        <input type="hidden" name="voucher_type" value="Journal">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">Voucher No <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="voucher_no" class="form-control"
                                    value="<?= (string) $next_no ?>" readonly style="background: #e9ecef;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="voucher_date" class="form-control" value="<?= date('Y-m-d') ?>"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">Financial Year</label>
                                <select name="fy_id" class="form-control" required>
                                    <?php foreach ($financial_years as $fy): ?>
                                        <option value="<?= $fy->fy_id ?>" <?= $fy->status == 1 ? 'selected' : '' ?>>
                                            <?= date('Y', strtotime($fy->from_date)) ?>-<?= date('y', strtotime($fy->to_date)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="voucherTable">
                                <thead class="bg-warning text-dark">
                                    <tr>
                                        <th width="10%">Dr/Cr</th>
                                        <th width="20%">Group</th>
                                        <th width="20%">Particulars</th>

                                        <th width="15%">Amount</th>
                                        <th width="5%"><span class="fa fa-trash"></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="row-item">
                                        <td>
                                            <select name="type[]" class="form-control type-select" required>
                                                <option value="1">Cr</option>
                                                <option value="2">Dr</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="group_id[]" class="form-control group-select" required>
                                                <option value="">Select Group</option>
                                                <?php foreach ($groups as $g): ?>
                                                    <option value="<?= $g->group_id ?>">
                                                        <?= htmlspecialchars($g->group_name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="ledger_id[]" class="form-control ledger-select" required>
                                                <option value="">Select Particular...</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="amount[]"
                                                class="form-control amount-input" placeholder="0.00" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                    class="fa fa-times"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-info btn-sm" id="addVoucherRow"><i
                                        class="fa fa-plus"></i> Add Line</button>
                            </div>
                        </div>
                        <hr>
                        <div class="row align-items-end">
                            <div class="col-md-7">
                                <label class="font-weight-bold">Narration</label>
                                <textarea name="narration" class="form-control" rows="2"
                                    placeholder="Overall voucher narration..."></textarea>
                            </div>
                            <div class="col-md-5 text-right">
                                <button type="submit" class="btn btn-success btn-lg px-5" id="submitBtn">
                                    <i class="fa fa-save"></i> Save Voucher
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <!-- page-body-wrapper closed by footer.php -->

    <style>
        .ledger-select+.select2-container .select2-selection {
            height: 38px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>

    <script>
        /* Voucher script - jQuery & Select2 loaded in header */
        $(document).ready(function () {
            function initSelect2(element) {
                if ($(element).hasClass('select2-hidden-accessible')) {
                    $(element).select2('destroy');
                }
                $(element).select2({ placeholder: "Search...", width: '100%' });
            }

            // Initialize Select2
            $('.group-select, .ledger-select').each(function () { initSelect2(this); });

            $(document).on('change', '.group-select', function () {
                var group_id = $(this).val();
                var $row = $(this).closest('tr');
                var $particularSelect = $row.find('.ledger-select');

                $particularSelect.html('<option value="">Loading...</option>').trigger('change');

                if (group_id) {
                    $.post('<?= base_url('admin/getParticularsByGroup') ?>', { group_id: group_id }, function (data) {
                        var options = '<option value="">Select Particular...</option>';
                        $.each(data, function (i, item) {
                            options += '<option value="' + item.id + '">' + item.name + '</option>';
                        });
                        $particularSelect.html(options).trigger('change');
                        initSelect2($particularSelect);
                    });
                } else {
                    $particularSelect.html('<option value="">Select Particular...</option>').trigger('change');
                }
            });

            $('#addVoucherRow').on('click', function (e) {
                e.preventDefault();
                var $tableBody = $('#voucherTable tbody');

                var $newRow = $('<tr class="row-item">' +
                    '<td><select name="type[]" class="form-control type-select" required><option value="1">Cr</option><option value="2">Dr</option></select></td>' +
                    '<td><select name="group_id[]" class="form-control group-select" required><option value="">Select Group</option><?php foreach ($groups as $g): ?><option value="<?= $g->group_id ?>"><?= htmlspecialchars($g->group_name) ?></option><?php endforeach; ?></select></td>' +
                    '<td><select name="ledger_id[]" class="form-control ledger-select" required><option value="">Select Particular...</option></select></td>' +
                    '<td><input type="number" step="0.01" name="amount[]" class="form-control amount-input" placeholder="0.00" required></td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>' +
                    '</tr>');

                $tableBody.append($newRow);
                initSelect2($newRow.find('.group-select'));
                initSelect2($newRow.find('.ledger-select'));

            });

            $(document).on('click', '.remove-row', function () {
                if ($('.row-item').length > 1) {
                    $(this).closest('tr').remove();
                }
            });
        });
    </script>

    <?php include("footer.php"); ?>