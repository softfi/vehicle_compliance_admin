<?php include("header.php");?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
            <?php include("mainsidebar.php"); ?>
                <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Overall Expenses  </h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->
                  <div class="container-fluid default-dashboard">
                    
                  <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-1@m">
                             <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                          <form action="<?php echo base_url(); ?>/Admin/insert_overalexpence" method="post" enctype="multipart/form-data">
                              <div class="uk-grid-small uk-child-width-expand" uk-grid>
        
                                                 <div class="uk-margin-bottom">
                                                    <label>Amount</label>
                                                    <input type="number" name="amount" placeholder="Enter Amount" id="amount" class="uk-input" />
                                                </div>
                                                
                                               <div class="uk-margin-bottom">
                                                    <label>Date</label>
                                                    <input type="date" name="date" id="date" class="uk-input" value="<?php echo date('Y-m-d'); ?>" />
                                                </div>

                                                
                                              
                                                 <div class="uk-margin-bottom">
                                                    <label>Location</label>
                                                     <select name="location_id" class="uk-input" required>
                                                       <option>Select Location</option>
                                                       <?php foreach($location as $loc){?>
                                                            <option value="<?=$loc->location_id?>"><?=$loc->location_name?></option>
                                                       <?php } ?>
                                                   </select>
                                                </div>
                                                <div class="uk-margin-bottom">
                                                    <label>Upload File</label>
                                                    <input type="file" name="upload_file" placeholder="Upload file " id="upload_file" class="uk-input" value="" />
                                                </div>
                                                
                                                <div class="uk-margin-bottom">
                                                    <label>Naration</label>
                                                    <input type="text" name="Naration" placeholder="Naration "  class="uk-input" value="" />
                                                </div>
                                                   <div class="uk-margin-bottom">
                                                    <label>Remark</label>
                                                    <input type="text" name="remark" placeholder="remark "  class="uk-input" value="" />
                                                </div>
                                                <div class="uk-margin-bottom">
                                                   <label class="uk-width-1-1">.</label>
                                                        <?php if(in_array(10.1,$jobAssign)){ ?>
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                        <?php }?>
                                                </div>
                                                </div>
                                                 
                                        </form>
                                </div>
                        </div>
                          <div class="uk-width-2-3@m">
                <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom">
                    <form method="post" action="<?php echo base_url(); ?>/Admin/Overall_Expence">
                        <?php
                        $default_from_date = $date['from_date'] ?? date('Y-m-01');
                        $default_to_date = $date['to_date'] ?? date('Y-m-d');
                        ?>
                        <div class="uk-grid-small uk-child-width-expand" uk-grid>
                            <div>
                                <label for="from_date">From Date:</label>
                                <input type="date" id="from_date" name="from_date" class="uk-input" value="<?= $default_from_date; ?>" />
                            </div>
                            <div>
                                <label for="to_date">To Date:</label>
                                <input type="date" id="to_date" name="to_date" class="uk-input" value="<?= $default_to_date; ?>" />
                            </div>
                            <div>
                                <label for="submit_button">.</label>
                                <button type="submit" class="uk-button uk-button-primary uk-width-1-1" id="submit_button">Filter</button>
                            </div>
                            <div>
                                <label for="download_excel">.</label>
                                <a href="#" class="uk-button uk-button-primary uk-width-1-1" id="download_excel">Download Excel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
                            <div class="uk-width-1-1@m">
                                <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                                     <div class="table-responsive custom-scrollbar custom-scrollbar">
                                    <table class="display" id="row_create" style="width:100%">
                                               <thead>
                                                   <tr>
                                                       <th>Sl no</th>
                                                       <th>Amount</th>
                                                       <th>Date</th>
                                                       <th>Location</th>
                                                       <th>File</th>
                                                       <th>Naration</th>
                                                        <th style="width:300px">Remark</th>
                                                       <!--<th>Edit</th>-->
                                                       <th>Action</th>
                                                     
                                                   </tr>
                                               </thead>
                                        <tbody>
                                            <?php 
                                            $i = 1;
                                            foreach($overall as $ovr){ 
                                                
                                                // Prepare file URL
                                                $file = $ovr->upload_file;

                                                // If already contains 'uploads/', use as full path
                                                if (strpos($file, 'uploads/') !== false) {
                                                    $fileUrl = base_url() . $file;
                                                } else {
                                                    // Otherwise prefix uploads/
                                                    $fileUrl = base_url() . 'uploads/' . $file;
                                                }
                                            ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= $ovr->amount; ?></td>
                                                <td><?= $ovr->date; ?></td>
                                                <td><?= $ovr->location_name; ?></td>

                                                <td><a href="<?= $fileUrl; ?>" target="_blank">File</a></td>

                                                <td><?= $ovr->narration; ?></td>
                                                <td><?= $ovr->remark; ?></td>

                                                <td>
                                                    <a href="<?= base_url(); ?>Admin/delete_overalexpence/<?= $ovr->id; ?>" 
                                                    class="btn btn-danger">Delete</a>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                        <tfoot>
                                            
                                             <tr>
                                                       <th>Sl no</th>
                                                       <th>Amount</th>
                                                       <th>Date</th>
                                                       <th>Location</th>
                                                       <th>File</th>
                                                       <th>Naration</th>
                                                       <th>Remark</th>
                                                       <!--<th>Edit</th>-->
                                                       <th>Delete</th>
                                                       
                                                       
                                                   </tr>
                                        </tfoot>
                                     </table>  
                                    </div>
                                </div> 
                            </div>
                          </div>
          <!-- Container-fluid Ends-->
        </div>
</div>  

// <script>
//     function downloadExcel() {
//         // Create a form element
//         var form = document.createElement('form');
//         form.method = 'POST';
//         form.action = '<?= base_url(); ?>/Admin/export_overalexpence_excel'; // Update with the correct endpoint for export

//         // Create a hidden input to include all table data
//         var inputTableData = document.createElement('input');
//         inputTableData.type = 'hidden';
//         inputTableData.name = 'table_data';
//         inputTableData.value = JSON.stringify(getTableData()); // Get table data as JSON

//         // Append inputs to the form
//         form.appendChild(inputTableData);

//         // Append form to the body
//         document.body.appendChild(form);

//         // Submit the form
//         form.submit();

//         // Remove the form from the document
//         document.body.removeChild(form);
//     }

//     function getTableData() {
//         var tableData = [];
//         var rows = document.querySelectorAll('#row_create tbody tr');
//         rows.forEach(row => {
//             var cells = row.querySelectorAll('td');
//             var rowData = Array.from(cells).map(cell => cell.innerText);
//             tableData.push(rowData);
//         });
//         return tableData;
//     }
// </script>

<script>    
        document.getElementById('download_excel').addEventListener('click', function() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const baseUrl = '<?php echo base_url(); ?>/Admin/export_overalexpence_excel';
        const url = `${baseUrl}?from_date=${fromDate}&to_date=${toDate}`;
        window.location.href = url;
    });</script>


        <!-- footer start-->
       <?php include("footer.php");?>