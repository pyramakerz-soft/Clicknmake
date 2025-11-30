<?php $__env->startSection('title'); ?>
Units
<?php $__env->stopSection(); ?>

<?php
$menuItems = [
['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('student.index')],
['label' => 'Assignment', 'icon' => 'fas fa-home', 'route' => route('student.assignment')],
['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
];
?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('components.sidebar', ['menuItems' => $menuItems], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_css'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
    crossorigin="anonymous">

<style>
    body {
        background-color: #f8fafc;
    }

    .header-card {
        border-radius: 18px;
        padding: 20px;
        background: linear-gradient(to right, #2E3646, #1F2533);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #17253e;
        border-bottom: 3px solid #525d6f;
        display: inline-block;
        margin-bottom: 1rem;
        padding-bottom: 0.3rem;
    }

    .resource-card {
        background-color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .resource-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .unit-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
    }

    .unit-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #17253e;
        margin-bottom: 6px;
    }

    .unit-link {
        text-decoration: none;
        font-weight: 600;
        color: #17253e;
    }

    .unit-link:hover {
        text-decoration: underline;
        color: #0f1827;
    }
</style>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('components.sidebar', ['menuItems' => $menuItems], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <!-- Header -->
    <div class="header-card d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <img src="<?php echo e($student->image ? asset($student->image) : asset('images/default_user.jpg')); ?>"
                class="rounded-circle me-3" width="70" height="70" style="object-fit: cover; border:3px solid white;">
            <div>
                <h4 class="mb-1"><?php echo e($student->username); ?></h4>
                <small class="text-light"><?php echo e($student->stage->name); ?></small>
            </div>
        </div>

        <button onclick="openEditModal('editPassword')" class="btn btn-light">
            <i class="fas fa-edit"></i>
        </button>
    </div>

    <!-- Breadcrumb -->
    <div class="text-secondary small mb-4">
        <i class="fa-solid fa-house"></i>
        <span class="mx-1">/</span>
        <span>Units</span>
    </div>

    <!-- Materials -->
    <?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <h3 class="section-title"><?php echo e($material->title); ?></h3>

    <div class="row g-4 mb-5">

        <?php $__empty_1 = true; $__currentLoopData = $material->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="resource-card">

                <?php if($unit->image): ?>
                <img src="<?php echo e($unit->image); ?>" class="unit-img">
                <?php else: ?>
                <div class="d-flex align-items-center justify-content-center unit-img bg-light text-muted">
                    No Image
                </div>
                <?php endif; ?>

                <div class="p-3">
                    <div class="unit-title"><?php echo e($unit->title); ?></div>

                    <a href="<?php echo e(route('student_units.unitContent', $unit->id)); ?>" class="unit-link">
                        View Details →
                    </a>
                </div>

            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-muted fst-italic">No units available.</p>
        <?php endif; ?>

    </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_js'); ?>
<script>
    function openEditModal(id) {
        document.getElementById(id).classList.remove("d-none");
    }

    function closeModal(id) {
        document.getElementById(id).classList.add("d-none");
    }
</script>
<?php $__env->stopSection(); ?>


<!-- Password Modal -->
<form action="<?php echo e(route('changeStudentPassword')); ?>" method="POST"
    id="editPassword"
    class="position-fixed top-0 start-0 w-100 h-100 d-none"
    style="background: rgba(0,0,0,0.6); z-index:1000;">
    <?php echo csrf_field(); ?>

    <div class="bg-white rounded-3 shadow-lg p-4 mx-auto mt-5" style="max-width: 500px;">
        <div class="d-flex justify-content-between mb-3">
            <h5>Edit Password</h5>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('editPassword')">Close</button>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" name="password" required class="form-control" placeholder="Enter new password">
        </div>

        <button class="btn btn-primary w-100">Save</button>
    </div>
</form>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u413666390/domains/pyramakerz-artifacts.com/public_html/LMS/resources/views/pages/student/dashboard/index.blade.php ENDPATH**/ ?>