<?php include("header.php"); ?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>

<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Edit Adjust Salary</h3>
                    </div>
                    <div class="col-sm-6 p-0"></div>
                </div>
            </div>
        </div>

        <div class="container-fluid default-dashboard">        
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <form id="driverForm" action="<?php echo base_url(); ?>/admin/update_adjust_salary" method="POST">
                            
                            <!-- Hidden field for ID -->
                            <div class="form-group">
                                <input type="hidden" class="form-control" name="id" value="<?= $adjustment['id']; ?>" required>
                            </div>

                            <!-- Driver Name Field -->
                            <div class="form-group">
                                <label for="driver">Driver:</label>
                                <input type="text" class="form-control" name="driver_name" value="<?= $adjustment['driver_name']; ?>" required>
                            </div>

                            <!-- Location Field -->
                            

                            <!-- Amount Field -->
                            <div class="form-group">
                                <label for="amount">Amount:</label>
                                <input type="number" class="form-control" name="amount" value="<?= $adjustment['amount']; ?>" required>
                            </div>
                            
                             <div class="form-group">
                                <label for="amount">Remark</label>
                                <input class="uk-input" id="remark" name="remark" type="text" placeholder="Enter remark" >
                            </div>
                            
                            <!-- Month Dropdown -->
                          <div class="form-group">
                                <label for="month">Month:</label>
                                <select class="form-control" name="month" required>
                                    <?php
                                    $months = [
                                        1 => "January", 2 => "February", 3 => "March", 4 => "April", 
                                        5 => "May", 6 => "June", 7 => "July", 8 => "August", 
                                        9 => "September", 10 => "October", 11 => "November", 12 => "December"
                                    ];
                                    
                                    // Loop through the months and compare with the month name from the database
                                    foreach ($months as $index => $month) { ?>
                                        <option value="<?= $index; ?>" <?= $month == $adjustment['month'] ? 'selected' : ''; ?>>
                                            <?= $month; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>


                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include("footer.php"); ?>
