<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Default Dashboard</h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="index.html">
                                    <svg class="stroke-icon">
                                        <use href="<?php echo base_url(); ?>/assets/admin/svg/icon-sprite.svg#stroke-home"></use>
                                    </svg>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Dashboard</li>
                            <li class="breadcrumb-item active">Default</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="row">
                <div class="col-xxl-4 col-xl-100 box-col-12 ps-4 pe-4 left-background">
                    <div class="row bg-light h-100 p-3 pt-4 pb-4">
                        <div class="col-12 col-xl-50 box-col-6">
                            <div class="card welcome-card">
                                <div class="card-body">
                                    <div class="d-flex"> 
                                        <div class="flex-grow-1"> 
                                            <h1>Hello, <?= $this->session->get('fullname'); ?></h1>
                                            <p>Welcome back! Let's start from where you left.</p>
                                            <a class="btn" href="<?php echo base_url(); ?>/Admin/Profile">View Profile</a>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <img src="<?php echo base_url(); ?>/assets/admin/images/dashboard/welcome.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                </div>
                
                <div class="col-xxl-8 col-xl-100 box-col-12 ps-8 pe-8 left-background">
                    <?php
                        $fitnessExpiryCount = $taxExpiryCount = $insExpiryCount = $permitExpiryCount = 0;
                        $nPermitExpiryCount = $amcExpiryCount = $i3msExpiryCount = $khanijExpiryCount = 0;

                        $fitnessExpiredCount = $taxExpiredCount = $insExpiredCount = $permitExpiredCount = 0;
                        $nPermitExpiredCount = $amcExpiredCount = $i3msExpiredCount = $khanijExpiredCount = 0;

                        $today = new DateTime();
                        $today->setTime(0, 0);

                        foreach ($vehicle as $v) {
                            $fitnessExpDate = new DateTime($v->fitness_exp_date);
                            $taxExpDate     = new DateTime($v->tax_exp_date);
                            $insExpDate     = new DateTime($v->ins_exp_date);
                            $permitExpDate  = new DateTime($v->permit_exp_date);
                            $nPermitExpDate = new DateTime($v->npermit_exp_date);
                            $amcExpDate     = new DateTime($v->amc_expary);
                            $i3msExpDate    = new DateTime($v->i3ms_expary);
                            $khanijExpDate  = new DateTime($v->khanij_expiri);

                            if ($fitnessExpDate >= $today && $fitnessExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $fitnessExpiryCount++;
                            }
                            if ($fitnessExpDate < $today) {
                                $fitnessExpiredCount++;
                            }

                            if ($taxExpDate >= $today && $taxExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $taxExpiryCount++;
                            }
                            if ($taxExpDate < $today) {
                                $taxExpiredCount++;
                            }

                            if ($insExpDate >= $today && $insExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $insExpiryCount++;
                            }
                            if ($insExpDate < $today) {
                                $insExpiredCount++;
                            }

                            if ($permitExpDate >= $today && $permitExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $permitExpiryCount++;
                            }
                            if ($permitExpDate < $today) {
                                $permitExpiredCount++;
                            }

                            if ($nPermitExpDate >= $today && $nPermitExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $nPermitExpiryCount++;
                            }
                            if ($nPermitExpDate < $today) {
                                $nPermitExpiredCount++;
                            }

                            if ($amcExpDate >= $today && $amcExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $amcExpiryCount++;
                            }
                            if ($amcExpDate < $today) {
                                $amcExpiredCount++;
                            }

                            if ($i3msExpDate >= $today && $i3msExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $i3msExpiryCount++;
                            }
                            if ($i3msExpDate < $today) {
                                $i3msExpiredCount++;
                            }

                            if ($khanijExpDate >= $today && $khanijExpDate <= (clone $today)->add(new DateInterval('P15D'))) {
                                $khanijExpiryCount++;
                            }
                            if ($khanijExpDate < $today) {
                                $khanijExpiredCount++;
                            }
                        }

                        $from_date = (new DateTime())->format('Y-m-d');
                        $to_date   = (new DateTime())->add(new DateInterval('P15D'))->format('Y-m-d');
                    ?>

                    <div class="uk-grid-small uk-child-width-1-4@m" uk-grid>
                        <!-- Fitness Expiry Card -->
                        <div>
                            <div class="card" id="fitnessExpiryCard">
                                <div class="uk-padding-small uk-alert-warning">
                                    <h4>Fitness Expiry</h4>
                                    <p>Expiring Soon: <?= $fitnessExpiryCount; ?></p>
                                    <p>Expired: <?= $fitnessExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'fitness');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <!-- Tax Expiry Card -->
                        <div>
                            <div class="card" id="taxExpiryCard">
                                <div class="uk-padding-small uk-alert-primary">
                                    <h4>Tax Expiry</h4>
                                    <p>Expiring Soon: <?= $taxExpiryCount; ?></p>
                                    <p>Expired: <?= $taxExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'road_tax');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <!-- Insurance Expiry Card -->
                        <div>
                            <div class="card" id="insExpiryCard">
                                <div class="uk-padding-small uk-alert-success">
                                    <h4>Insurance Expiry</h4>
                                    <p>Expiring Soon: <?= $insExpiryCount; ?></p>
                                    <p>Expired: <?= $insExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'insurance');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <!-- Permit Expiry Card -->
                        <div>
                            <div class="card" id="permitExpiryCard">
                                <div class="uk-padding-small uk-alert-danger">
                                    <h4>Permit Expiry</h4>
                                    <p>Expiring Soon: <?= $permitExpiryCount; ?></p>
                                    <p>Expired: <?= $permitExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'permit');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <!-- National Permit Expiry Card -->
                        <div>
                            <div class="card" id="nPermitExpiryCard">
                                <div class="uk-padding-small uk-alert-danger">
                                    <h4>National Permit Expiry</h4>
                                    <p>Expiring Soon: <?= $nPermitExpiryCount; ?></p>
                                    <p>Expired: <?= $nPermitExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'npermit');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <!-- AMC Expiry Card -->
                        <div>
                            <div class="card" id="amcExpiryCard">
                                <div class="uk-padding-small uk-alert-warning">
                                    <h4>AMC Expiry</h4>
                                    <p>Expiring Soon: <?= $amcExpiryCount; ?></p>
                                    <p>Expired: <?= $amcExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'amc');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <!-- I3MS Expiry Card -->
                        <div>
                            <div class="card" id="i3msExpiryCard">
                                <div class="uk-padding-small uk-alert-success">
                                    <h4>I3MS Expiry</h4>
                                    <p>Expiring Soon: <?= $i3msExpiryCount; ?></p>
                                    <p>Expired: <?= $i3msExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'ims');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <!-- Khanij Expiry Card -->
                        <div>
                            <div class="card" id="khanijExpiryCard">
                                <div class="uk-padding-small uk-alert-primary">
                                    <h4>Khanij Expiry</h4>
                                    <p>Expiring Soon: <?= $khanijExpiryCount; ?></p>
                                    <p>Expired: <?= $khanijExpiredCount; ?></p>
                                    <a href="javascript:void(0);" 
                                       onClick="Filter_data('<?= $from_date; ?>', '<?= $to_date; ?>', 'Khanij');" 
                                       class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>   
            </div>
        </div>
        <!-- Container-fluid Ends-->
        <!-- Task Management Table -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Task Management</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Assigned To</th>
                            <th>Assigned By</th>
                            <th>Created Date</th>
                            <th>Completion Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($tasks)): ?>
                            <?php foreach($tasks as $task): ?>
                                <tr>
                                    <td><?= $task->id ?></td>
                                    <td><?= $task->task_description ?></td>
                                    <td><?= $task->assigned_to_name ?></td>
                                    <td><?= $task->assigned_by_name ?></td>
                                    <td><?= $task->created_date ?></td>
                                    <td><?= $task->completion_date ?></td>
                                    <td>
                                        <?php 
                                            switch ($task->status) {
                                                case 1: echo "Pending"; break;
                                                case 2: echo "In Progress"; break;
                                                case 3: echo "Complete"; break;
                                            }
                                        ?>
                                    </td>
                                    <td><?= $task->remarks ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No tasks found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- End Task Management Table -->

    </div>
    
    <!-- footer start-->
    <form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url(); ?>/admin/vehicle" method="post">
        <input type="hidden" name="from_date" id="from_date" value="">
        <input type="hidden" name="to_date" id="to_date" value="">
        <input type="hidden" name="type" id="type" value="">
    </form>

    <script type="text/javascript">
        function Filter_data(from_date, to_date, type) {
            document.getElementById("from_date").value = from_date;
            document.getElementById("to_date").value = to_date;
            document.getElementById("type").value = type;
            document.getElementById("frm_deleteBanner").submit();
        }
    </script>

    <?php include("footer.php"); ?>
