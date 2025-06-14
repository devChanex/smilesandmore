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
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="card shadow mb-12">
                        <div class="card-header <?php echo $cards; ?>">
                            View Patient Consent

                            <!-- <button id="divPrinter" class="btn btn-success btn-sm btn-circle float-right"
                                onclick="printDiv('bodyResult')" title="Print E-SOA"><i
                                    class="fas fa-print"></i></button> -->
                        </div>
                        <div class="card-body" id="bodyResult">
                            <input type="hidden" value="<?php echo $_GET['consentid']; ?>" id="consentId">
                            <input type="hidden" value="<?php echo $_GET['clientid']; ?>" id="clientId">

                            <div class="col-lg-12 d-flex align-items-center">
                                <img src="img/white_logo_final.jpg" alt="Logo"
                                    style="max-height:100px; margin-right: 20px;">
                                <div>
                                    <h4 class="mb-1"><strong>Dr. Nikki Sarmiento Dental Care Clinic</strong>
                                    </h4>
                                    <p class="mb-0">Stall B Josefa St. Josefaville 1 Subd Brgy Malabanias
                                        Angeles
                                        City Pampanga PH 2009</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-2">Name: </strong>
                                    <?php echo trim($_GET["lname"]) . ', ' . trim($_GET['fname']) . ' ' . trim($_GET['mname']); ?>
                                </div>
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Address: </strong>
                                    <?php echo trim($_GET["homeAddress"]); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Birthdate: </strong>
                                    <?php echo trim($_GET["birthDate"]); ?>
                                </div>
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Age: </strong>
                                    <?php

                                    $dob = new DateTime($_GET["birthDate"]); // assuming dob is something like '1990-04-15'
                                    $today = new DateTime();
                                    $age = $today->diff($dob)->y;
                                    echo $age;


                                    ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Gender: </strong>
                                    <?php echo trim($_GET["sex"]); ?>
                                </div>
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Occupation: </strong>
                                    <?php echo trim($_GET["occupation"]); ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Civil Status: </strong>
                                    <?php echo trim($_GET["civilStatus"]); ?>
                                </div>
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Religion: </strong>
                                    <?php echo trim($_GET["religion"]); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Contact Number: </strong>
                                    <?php echo trim($_GET["mobileNumber"]); ?>
                                </div>
                                <div class="col-lg-6 d-flex align-items-center">
                                    <strong class="mr-3">Referred By: </strong>
                                    <?php echo trim($_GET["refferedBy"]); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">

                                <div class="col-lg-12" id="medHistory">


                                </div>
                            </div>

                            <br>
                            <hr>
                            <!-- Page Heading -->
                            <h1 class="h3 mb-4  text-center">Informed Consent</h1>

                            <!-- Consent Card -->


                            <div class="text-justify" style="margin:50px;">

                                <p><strong>TREATMENT TO BE DONE:</strong> I understand and consent to have any treatment
                                    done by the dentist after the procedure, the risks & benefits & cost have been fully
                                    explained. These treatments include, but are not limited to, x-rays, cleanings,
                                    periodontal treatments, fillings, crowns, bridges, all types of extraction, root
                                    canals, and or dentures, local anesthetics & surgical cases.</p>

                                <p><strong>DRUGS & MEDICATIONS:</strong> I understand that antibiotics, analgesics, and
                                    other medications can cause allergic reactions like redness and swelling of tissues,
                                    pain, itching, vomiting, and or anaphylactic shock. </p>

                                <p><strong>CHANGES IN TREATMENT PLAN:</strong> I understand that during treatment it may
                                    be necessary to change and add procedures because of conditions found while working
                                    on the teeth that were not discovered during examination. For example, root canal
                                    therapy may be needed following routine restorative procedures. I give my permission
                                    to the dentist to make any/all changes and additions as necessary with my
                                    responsibility to pay all the costs agreed.
                                </p>

                                <p><strong>RADIOGRAPH:</strong> I understand that an x-ray shot or a radiograph may be
                                    necessary as part of diagnostic aid to come up with tentative diagnosis of my dental
                                    problem and to make a good treatment plan but this will not give me a 100% assurance
                                    for the accuracy of the treatment since all dental treatments are subject to
                                    unpredictable complications that later on may lead to sudden change of treatment
                                    plan and subject to new charges.</p>

                                <p><strong>REMOVAL OF TEETH:</strong> I understand the alternatives to tooth removal
                                    (root canal therapy, crowns & periodontal surgery, etc.) and I completely understand
                                    these alternatives, including their risk and benefits prior to authorizing the
                                    dentist to remove teeth and any other structures necessary for reasons above. I
                                    understand that removing teeth does not always remove all the infections, if present
                                    and it may be necessary to have further treatment. I understand the risk involved in
                                    having teeth removed, such as pain, swelling, spread of infection, dry socket, and
                                    fractured jaw, loss of feeling on the teeth, lips, tongue and surrounding tissue
                                    that can last for an indefinite period of time. I understand that I may need further
                                    treatment under a specialist if complications arise during or following treatment.
                                </p>

                                <p><strong>CROWNS AND BRIDGES:</strong> Preparing a tooth may irritate the nerve tissue
                                    in the center of the tooth, leaving the tooth extra sensitive to heat, cold &
                                    pressure. Treating such irritation may involve using special toothpastes, mouth
                                    rinses or root canal therapy. I understand that sometimes it is not possible to
                                    match the color of natural teeth exactly with artificial teeth and further
                                    understand that I may be wearing temporary crowns which may come off easily and that
                                    I must be careful to ensure that they are kept on until the permanent crowns are
                                    delivered. It is my responsibility to return for permanent cementation within 20
                                    days from tooth preparation, as excessive delay may allow for tooth movement, which
                                    may necessitate a remake of the crown, bridge and cap. I understand there will be
                                    additional charges for remakes due to my delaying of permanent cementation. I
                                    realize that final opportunity to make changes in my new crown, bridges or cap
                                    (including shape, fit, size & color) will be before permanent cementation.
                                </p>

                                <p><strong>ENDODONTICS (ROOT CANAL):</strong> I understand there is no guarantee that a
                                    root canal treatment will save a tooth & that complications can occur from the
                                    treatment and that occasionally root canal filling materials may extend through the
                                    tooth which does not necessarily affect the success of the treatment. I understand
                                    that endodontic files & drills are very fine instruments & stresses vented in their
                                    manufacture and calcifications present in teeth can cause them to break during use.
                                    I understand that referral to the endodontist for additional treatments may be
                                    necessary following any root canal treatment & I agree that I am responsible for any
                                    additional cost for treatment performed by the endodontist. I understand that a
                                    tooth may require removal in spite of all efforts to save it. </p>

                                <p><strong>PERIODONTAL DISEASE:</strong> I understand that periodontal disease is a
                                    serious condition causing gum & bone inflammation or loss and that can lead
                                    eventually to the loss of my teeth. I understand the alternative treatment plans to
                                    correct periodontal disease, including gum surgery, tooth extractions with or
                                    without replacement. I understand that undertaking any dental procedures may have
                                    future adverse effect on my periodontal conditions. </p>

                                <p><strong>FILLINGS:</strong> I understand that care must be exercised in chewing on
                                    fillings, especially during the first 24 hours to avoid breakage. I understand that
                                    a more extensive filling or a crown may be required, as additional decay or fracture
                                    may become evident after initial excavation. I understand that significant
                                    sensitivity is a common but usually temporary, after-effect of a newly placed
                                    filling. I further understand that filling a tooth may irritate the nerve tissue
                                    creating sensitivity & treating such sensitivity could require root canal therapy or
                                    extractions.</p>

                                <p><strong>DENTURES:</strong> I understand that wearing of dentures can be difficult.
                                    Sore spots, altered speech and difficulty in eating are common problems. Immediate
                                    dentures (placement of denture immediately after extractions) may be painful and may
                                    require considerable adjusting in several relines. I understand that it is my
                                    responsibility to return for delivery of dentures. I understand that failure to keep
                                    my delivery appointment may result in poorly fitted dentures. If a remake is
                                    required due to my delays of more than 30 days, there will be additional charges. A
                                    permanent reline will be needed later, which is not included in the initial fee. I
                                    understand that all adjustment or alterations of any kind after this initial period
                                    is subject to charges.</p>

                                <p><strong>I understand that Dentistry is not an exact science and that no dentist can
                                        properly guarantee accurate results all the time.</strong></p>

                                <p>I hereby authorize any of the doctors/dental auxiliaries to proceed with and perform
                                    the dental restorations and treatments as explained to me. I understand that these
                                    are subject to modification depending on undiagnosable circumstances that may arise
                                    during the course of treatment.</p>

                                <p>I understand that regardless of any dental insurance coverage I may have, I am
                                    responsible for payment of dental fees. I agree to pay any attorney's fees,
                                    collection fee, or court costs that may be incurred to satisfy any obligation to
                                    this office.</p>

                                <p>All treatment was properly explained to me and in case of any untoward circumstances
                                    that may arise during the procedure, the attending dentist will not be held liable
                                    since it is my free will, with full trust and confidence in him/her to undergo
                                    dental treatment under his/her care.</p>

                            </div>

                            <!-- Signature Area -->
                            <form class="mt-4">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        Patient's/Guardian's Name:
                                        <?php
                                        if ($_GET["age"] < 18) {
                                            echo $_GET["guardianName"];
                                        } else {
                                            echo $_GET["fname"] . ' ' . $_GET["mname"] . ' ' . $_GET["lname"];
                                        }
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-6"><label>Patient's/Guardian's Signature</label></div>
                                            <div class="col-lg-6"><label>Date Signed:
                                                    <?php echo $_GET["date"]; ?></label>
                                            </div>
                                        </div>
                                        <div class="border rounded p-3 signature-box"
                                            style="height: 80px; cursor: pointer;" id="patient-signature-box">
                                            <img src="" alt="signature"
                                                style="width: 100%; height: 100%; object-fit: contain;"
                                                id="patientSignature">
                                        </div>

                                    </div>
                                    <div class="form-group col-md-6">
                                        Dentist Name : <?php echo $_GET["dentist"]; ?>
                                        <br>

                                        <label>Dentist Signature</label>
                                        <div class="border rounded p-3 signature-box"
                                            style="height: 80px; cursor: pointer;" id="dentist-signature-boxs">
                                            <img src="img/e-sign.png" alt="signature"
                                                style="width: 100%; height: 100%; object-fit: contain;"
                                                id="dentistSignatures">
                                        </div>
                                    </div>

                                </div>



                            </form>





                            <footer class="sticky-footer">
                                <div class="container my-auto">
                                    <div class="copyright text-center my-auto">

                                        <a href="javascript:void(0)" class="btn btn-danger btn-icon-split"
                                            onclick="window.location.href='consentList.php'">
                                            <span class="icon text-white-50"><i class="fas fa-fw fa-times"></i></span>
                                            <span class="text">Back</span>
                                        </a>
                                    </div>
                                </div>
                            </footer>


                            <!-- END OF YOUR ADDITIONAL CODE SNIPPET -->
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

            <!-- Custom scripts for all pages-->
            <script src="js/sb-admin-2.min.js"></script>
            <script src="controllers/logOutConroller.js"></script>
            <script src="controllers/sessionController.js"></script>
            <script src="controllers/consentViewController.js"></script>
            <script src="controllers/divPrinterController-v1.js"></script>

            <script src="js/custom-v1.js"></script>
</body>

</html>