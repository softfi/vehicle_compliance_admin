<?php include("header.php");?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- jQuery (Required) -->
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    thead th {
        white-space: nowrap;
    }
</style>
      <!-- Page Body Start-->
      <div class="page-body-wrapper" style="background:#ececec;">
        <?php include("mainsidebar.php"); ?>
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Statutory Entry  </h3>
                </div>
                <div class="col-sm-6 p-0">
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                        <form action="<?php echo base_url(); ?>/Admin/insert_Statutory" method="post" enctype="multipart/form-data">
                            <div class="uk-grid-small uk-child-width-expand" uk-grid>
                                <div>
                                    <label>Vehicle No.</label>
                                    <select class="form-control" id="single" name="vehicle_no">
                                        <option value="trip">select</option>
                                        <?php foreach($vehicle as $vec){?>
                                        <option value="<?=$vec->id;?>"><?=$vec->vehicle_no;?></option>
                                        <?php } ?>
                                    </select>
                                </div> 
                                <div>
                                    <label>Type</label>
                                    <select class="form-control" name="type" onchange="showFileInput(this.value)">
                                        <option value="">-- Select Type --</option>
                                        <option value="road_tax">Road Tax</option>
                                        <option value="insurance">Insurance</option>
                                        <option value="Fitness">Fitness</option>
                                        <option value="permit">Permit</option>
                                        <option value="national_permit">National Permit</option>
                                        <option value="pucc">PUCC</option>
                                        <option value="AMC">AMC</option>
                                        <option value="rto_penalty">RTO Pennlty</option>
                                        <option value="Servicing">Servicing</option>
                                        <option value="I3MS">I3MS RECHARGE</option>
                                        <option value="KHANIJ">KHANIJ EXPIRI</option>
                                    </select>
                                </div> 
                                <div class="uk-margin-bottom">
                                    <label>Amount</label>
                                    <input type="number" name="Amount" placeholder="Amount" id="Amount" class="uk-input" value="" />
                                </div>
                                <div class="uk-margin-bottom">
                                    <label>Expary Date</label>
                                    <input type="date" name="exp_date" placeholder="Expary Date" id="exp_date" class="uk-input" value="" />
                                </div>
                                <div class="uk-margin-bottom">
                                    <label>Done By</label>
                                    <input type="text" name="done_by" placeholder="Done  By" id="done_by" class="uk-input" value="" />
                                </div>
                            </div>
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-margin-bottom" id="fileInputs">
                                   
                                </div>
                                <div class="uk-margin-bottom">
                                    <label class="uk-padding-small"></label>
                                    <?php if(in_array(14.1,$jobAssign)){ ?>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <?php }?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="uk-width-1-1@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <div class="table-responsive custom-scrollbar">
                            <table class="display" id="row_create" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>Vehicle no</th>
                                        <th>Expence type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Done by</th>
                                        <th>Document</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    foreach($satutary as $sat) { 
                                        $doc_link = '-';
                                        $type = strtolower($sat->expence_type);
                            
                                        // Show document link based on type
                                        if ($type == 'road_tax' && $sat->road_tax_doc) {
                                            $doc_link = '<a href="' . base_url('uploads/documents/' . $sat->road_tax_doc) . '" target="_blank">View</a>';
                                        } elseif ($type == 'insurance' && $sat->insurance_doc) {
                                            $doc_link = '<a href="' . base_url('uploads/documents/' . $sat->insurance_doc) . '" target="_blank">View</a>';
                                        } elseif ($type == 'fitness' && $sat->fitness_doc) {
                                            $doc_link = '<a href="' . base_url('uploads/documents/' . $sat->fitness_doc) . '" target="_blank">View</a>';
                                        } elseif ($type == 'permit' && $sat->permit_doc) {
                                            $doc_link = '<a href="' . base_url('uploads/documents/' . $sat->permit_doc) . '" target="_blank">View</a>';
                                        } elseif ($type == 'npermit' && $sat->national_permit_doc) {
                                            $doc_link = '<a href="' . base_url('uploads/documents/' . $sat->national_permit_doc) . '" target="_blank">View</a>';
                                        } elseif ($type == 'pucc' && $sat->PUCC_doc) {
                                            $doc_link = '<a href="' . base_url('uploads/documents/' . $sat->PUCC_doc) . '" target="_blank">View</a>';
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= $sat->vehicle_no ?></td>
                                        <td><?= $sat->expence_type ?></td>
                                        <td><?= $sat->amount ?></td>
                                        <td><?= $sat->expary_date ?></td>
                                        <td><?= $sat->done_by ?></td>
                                        <td><?= $doc_link ?></td>
                                        <td>
                                            <?php if (in_array(14.3, $jobAssign)) { ?>
                                                <a href="javascript:void(0);" 
                                                   class="uk-button uk-button-small uk-button-primary openEditModal"
                                                   data-id="<?= $sat->statutory_id ?>">
                                                   Edit
                                                </a>
                                            <?php } ?>
                                            <?php if (in_array(14.2, $jobAssign)) { ?>
                                                <a href="<?= base_url('Admin/deletesat/'.$sat->statutory_id); ?>" onclick="return confirm('Are you sure you want to delete this record?');" class="uk-button uk-button-small uk-button-danger">Delete</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>Vehicle no</th>
                                        <th>Expence type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Done by</th>
                                        <th>Document</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer start-->
         </div>
        </div>
        <div id="modal-example" uk-modal>
            <div class="uk-modal-dialog uk-modal-body" style="width: 800px; max-width: 100%;">
                <h2 class="uk-modal-title">Edit Statutory Entry</h2>
                <form action="<?= base_url(); ?>/Admin/update_Statutory" method="post" enctype="multipart/form-data">
                    <div class="uk-grid-small uk-child-width-expand" uk-grid>
                       
                        <div>
                            <label>Vehicle No.</label>
                            <select class="form-control select2" id="edit_vehicle_no" name="vehicle_no">
                                <option value="trip">select</option>
                                <?php foreach($vehicle as $vec){?>
                                <option value="<?=$vec->id;?>"><?=$vec->vehicle_no;?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label>Type</label>
                            <select class="form-control" name="type" id="edit_type" onchange="showFileInputModal(this.value)" required>
                                <option value="">-- Select Type --</option>
                                <option value="road_tax">Road Tax</option>
                                <option value="insurance">Insurance</option>
                                <option value="Fitness">Fitness</option>
                                <option value="permit">Permit</option>
                                <option value="national_permit">National Permit</option>
                                <option value="pucc">PUCC</option>
                                <option value="AMC">AMC</option>
                                <option value="rto_penalty">RTO Penalty</option>
                                <option value="Servicing">Servicing</option>
                                <option value="I3MS">I3MS Recharge</option>
                                <option value="KHANIJ">KHANIJ Expiry</option>
                            </select>
                        </div> 
                        <div>
                            <label>Amount</label>
                            <input type="number" name="Amount" id="edit_amount" class="uk-input" required>
                        </div>
                        <div>
                            <label>Expiry Date</label>
                            <input type="date" name="exp_date" id="edit_exp_date" class="uk-input" required>
                        </div>
                        <div>
                            <label>Done By</label>
                            <input type="text" name="done_by" id="edit_done_by" class="uk-input" required>
                        </div>
                        <input type="hidden" name="statutory_id" id="edit_statutory_id">
                    </div>
                    <div class="uk-grid-small" uk-grid>
                        <div id="fileInputsModal" class="uk-width-1-1">
                            <!-- File input will be injected here based on type -->
                        </div>
                        <div class="uk-width-1-1 uk-text-right">
                            <button type="button" class="uk-button uk-button-default uk-modal-close">Cancel</button>
                            <?php if (in_array(14.1, $jobAssign)): ?>
                                <button type="submit" class="uk-button uk-button-primary">Submit</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            $(document).on('click', '.openEditModal', function () {
                var id = $(this).data('id');
            
                $.ajax({
                    url: "<?= base_url('Admin/editsat/') ?>" + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            const s = data.data;
            
                            $('#edit_statutory_id').val(s.statutory_id);
                            $('#edit_vehicle_no').val(s.vehicle_id);
                            $('#edit_type').val(s.expence_type);
                            $('#edit_amount').val(s.amount);
                            $('#edit_exp_date').val(s.expary_date);
                            $('#edit_done_by').val(s.done_by);
            
                            // Optional: trigger onchange to show file input
                            showFileInputModal(s.expence_type);
            
                            UIkit.modal('#modal-example').show();
                        } else {
                            alert('No data found!');
                        }
                    },
                    error: function () {
                        alert('Error loading data!');
                    }
                });
            });
        </script>
        <script>
            function showFileInput(docType) {
                const fileInputs = {
                    road_tax: "road_tax",
                    insurance: "insurance",
                    Fitness: "Fitness",
                    permit: "permit",
                    national_permit: "national_permit",
                    pucc: "pucc"
                };
                
                if (fileInputs[docType]) {
                    let html = `
                        <label>${fileInputs[docType]}</label>
                        <input type="file" name="${docType}" class="uk-input" required />
                    `;
                    document.getElementById('fileInputs').innerHTML = html;
                } else {
                    document.getElementById('fileInputs').innerHTML = '';
                }
            }
        </script>
        <script>
            function showFileInputModal(docType) {
                const fileInputs = {
                    road_tax: "road_tax",
                    insurance: "insurance",
                    Fitness: "Fitness",
                    permit: "permit",
                    national_permit: "national_permit",
                    pucc: "pucc"
                };
            
                if (fileInputs[docType]) {
                    let html = `
                        <label>${fileInputs[docType]} Document</label>
                        <input type="file" name="${docType}" class="uk-input" />
                    `;
                    document.getElementById('fileInputsModal').innerHTML = html;
                } else {
                    document.getElementById('fileInputsModal').innerHTML = '';
                }
            }
        </script>
        
 
       <?php include("footer.php");?>
