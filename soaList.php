<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Smiles & More</title>
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <link href="css/custom.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sortable.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include_once('bars/sidebar.php'); ?>


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include_once('bars/topbar.php'); ?>


                <!-- Begin Page Content -->
                <div class="container-fluid" id="content-table">

                    <!-- Page Heading -->
                    <div class="card shadow mb-12">
                        <div class="card-header py-3 <?php echo $cards; ?>">
                            <h6 class="m-0 font-weight-bold">E-SOA / XRay Attachment</h6>

                        </div>
                        <div class="card-body">

                            <!-- USE THIS SPACE FOR YOUR ADDITIONAL CODE SNIPPET -->
                            <div class="card-body">
                                <div class="card-header py-3 d-flex justify-content-between">
                                    <h6 class="m-0 font-weight-bold"></h6>
                                    <div class="d-flex align-items-center gap-2 ms-auto">
                                        <strong>Search: </strong><input type="search" id="tableSearch"
                                            class="form-control form-control-sm" placeholder="" style="width: 300px;"
                                            oninput="search();">

                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered text-dark" id="sortableTable" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th onclick="sortTable(this)">Date</th>
                                                <th onclick="sortTable(this)">Time</th>
                                                <th onclick="sortTable(this)">Patient</th>
                                                <th onclick="sortTable(this)">Dentist</th>
                                                <th onclick="sortTable(this)">Total</th>
                                                <th onclick="sortTable(this)">Balance</th>
                                                <th onclick="sortTable(this)">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody id="resultResponseBody">



                                        </tbody>
                                    </table>
                                </div>
                                <input type="hidden" id="currentPage" value="1">
                                <div id="pagination"></div>
                            </div>






                            <!-- END OF YOUR ADDITIONAL CODE SNIPPET -->
                        </div>
                    </div>
                    <div class="modal fade" id="editModal" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">

                        <div class="modal-dialog modal-xl " role="document">
                            <div class="modal-content ">
                                <form id="editForm">
                                    <div class="modal-header <?php echo $cards; ?>">
                                        <h5 class="modal-title" id="editModalLabel">Edit SOA Details</h5>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="soaid" id="edit-soaid">

                                        <div class="form-group">
                                            <label>Dentist</label>
                                            <select name="dentist" id="edit-dentist" class="form-control">
                                                <?php
                                                foreach ($dentist as $d) {
                                                    echo '<option value="' . htmlspecialchars($d) . '">' . htmlspecialchars($d) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Date</label>
                                            <input type="date" class="form-control" name="remarks" id="edit-date">
                                        </div>
                                        <div class="form-group">
                                            <label>Time</label>
                                            <input type="text" class="form-control" name="remarks" id="edit-time">
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" onclick="updateSoa()">Save
                                            changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <?php include_once('bars/footer.php'); ?>

            <!-- Bootstrap core JavaScript-->
            <script src="vendor/jquery/jquery.min.js"></script>
            <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Page level plugins -->
            <script src="vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

            <!-- Page level custom scripts -->
            <script src="js/demo/datatables-demo.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="js/sb-admin-2.min.js"></script>
            <script src="controllers/logOutConroller.js"></script>
            <script src="controllers/sessionController.js"></script>
            <script src="controllers/getSoaListController.js"></script>
            <script src="js/custom-v2.js"></script>
            <script src="js/sortable.js"></script>


</body>

</html>