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

<?php $__env->startSection('content'); ?>
    <div class="p-6">
        <!-- Student Header Card -->
        <div class="rounded-2xl flex items-center justify-between py-4 px-6 bg-gradient-to-r from-[#2E3646] to-[#1F2533] shadow-lg">
            <div class="flex items-center space-x-5">
                <img class="w-20 h-20 rounded-full object-cover ring-2 ring-white shadow-md"
                     alt="avatar"
                     src="<?php echo e($student->image ? asset($student->image) : asset('images/default_user.jpg')); ?>" />
                <div class="font-semibold text-white flex flex-col space-y-1">
                    <h2 class="text-2xl"><?php echo e($student->username); ?></h2>
                    <p class="text-gray-300"><?php echo e($student->stage->name); ?></p>
                </div>
            </div>

            <button onclick="openEditModal('editPassword')" class="hover:scale-110 transition-transform">
                <i class="fas fa-edit text-white text-xl"></i>
            </button>
        </div>

        <!-- Breadcrumb -->
        <div class="p-3 text-[#667085] my-6 flex items-center text-sm">
            <i class="fa-solid fa-house mx-2"></i>
            <span class="text-[#D0D5DD] mx-1">/</span>
            <a href="#" class="mx-1 cursor-pointer hover:underline hover:text-[#2E3646] transition-colors">Units</a>
        </div>

        <!-- Materials & Units -->
        <?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mb-12">
                <!-- Material Header -->
                <div class="flex items-center mb-4">
                    <i class="fa-solid fa-book text-[#2E3646] text-xl mr-3"></i>
                    <h2 class="text-2xl font-bold text-[#2E3646]"><?php echo e($material->title); ?></h2>
                </div>
                <hr class="border-t border-gray-200 mb-6">

                <!-- Units Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__empty_1 = true; $__currentLoopData = $material->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                            <div class="relative">
                                <?php if($unit->image): ?>
                                    <img src="<?php echo e($unit->image); ?>" alt="<?php echo e($unit->title); ?>"
                                         class="object-cover w-full h-56 group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                    <div class="flex items-center justify-center h-56 bg-gray-100 text-gray-400 text-lg">
                                        No Image
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-4">
                                <h3 class="text-xl font-semibold text-gray-800 truncate mb-2">
                                    <?php echo e($unit->title); ?>

                                </h3>
                                <div class="flex justify-between items-center">
                                    <a href="<?php echo e(route('student_units.unitContent', $unit->id)); ?>"
                                       class="text-[#2E3646] text-sm font-medium hover:text-[#1F2533] transition-colors">
                                        View Details
                                    </a>
                                    <i class="fa-solid fa-arrow-right text-[#2E3646] text-sm"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-gray-500 italic">No units available for this material.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_js'); ?>
    <script>
        function openModal(id, filePath) {
            let modalContent = `
            <embed src="${filePath}" width="100%" height="90%" />
            <img src="<?php echo e(asset('assets/img/watermark 2.png')); ?>" 
                class="absolute inset-0 w-full h-full opacity-50 z-10"
                style="pointer-events: none;">
        `;
            document.getElementById(id + '-content').innerHTML = modalContent;
            document.getElementById(id).classList.remove("hidden");
        }

        function closeModal(id) {
            document.getElementById(id).classList.add("hidden");
        }
    </script>
<?php $__env->stopSection(); ?>

<form action="<?php echo e(route('changeStudentPassword')); ?>" method="POST" id="editPassword"
    class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-10 hidden">
    <?php echo csrf_field(); ?>
    <div class="bg-white rounded-lg shadow-lg  w-[50%]">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                Edit password
            </h3>
            <div class="flex justify-end">
                <button onclick="closeModal('editPassword')" type="button"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Close</button>
            </div>
        </div>

        <div class="px-3 mb-3">
            <div class="rounded-2xl bg-[#F6F6F6] text-start px-4 md:px-6 py-3 md:py-4 my-4 md:my-5">
                <p class="font-semibold text-base md:text-lg text-[#1C1C1E]">Password</p>
                <input placeholder="Change Your Password" name="password" required
                    class="w-full rounded-2xl p-2 md:p-4 mt-5 text-sm md:text-base" type="password"
                    value="">
            </div>

            <button class="bg-[#17253E] font-bold text-base md:text-lg text-white rounded-2xl py-3 px-4 md:px-7"
                type="submit">Save</button>
        </div>

    </div>
</form>

<script>
    function openEditModal(id) {
        document.getElementById(id).classList.remove("hidden");
    }

    function closeModal(id) {
        document.getElementById(id).classList.add("hidden");
    }
</script>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u413666390/domains/pyramakerz-artifacts.com/public_html/LMS/lms_pyramakerz/resources/views/pages/student/dashboard/index.blade.php ENDPATH**/ ?>