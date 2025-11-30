<?php $__env->startSection('title'); ?>
    <?php echo e($unit->title); ?> Resources
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
    <div class="p-3">
        <div class="rounded-lg flex items-center justify-between py-3 px-6 bg-[#2E3646]">
            <div class="flex items-center space-x-4">
                <div>
                    <img class="w-20 h-20 rounded-full object-cover" alt="avatar"
                        src="<?php echo e($userAuth->image ? asset($userAuth->image) : asset('images/default_user.jpg')); ?>" />
                </div>
                <div class="ml-3 font-semibold text-white flex flex-col space-y-2">
                    <div class="text-xl"><?php echo e($userAuth->username); ?></div>
                    <div class="text-sm"><?php echo e($userAuth->stage->name); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 text-[#667085] my-8">
        <i class="fa-solid fa-house mx-2"></i>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="<?php echo e(route('student.index')); ?>" class="mx-2 cursor-pointer">Units</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="#" class="mx-2 cursor-pointer"><?php echo e($unit->title); ?></a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="#" class="mx-2 cursor-pointer">Resources</a>
    </div>

    <div class="flex flex-wrap p-3 justify-center">
        
        <?php if($unit->ebook_path): ?>
            <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-2 mx-2 bg-white rounded-xl">
                <a onclick="openModal('ebook', '<?php echo e(asset('ebooks/' . $unit->ebook_path . '/index.html')); ?>')" class="cursor-pointer block">
                    <h3 class="px-4 py-2 bg-gray-200 text-lg font-bold truncate">E-Book</h3>
                    <div class="p-4">
                        <img src="<?php echo e(asset($unit->image)); ?>" alt="EBook"
                            class="object-cover w-full h-[250px] rounded-xl">
                    </div>
                </a>
            </div>
        <?php endif; ?>

        
        <?php if($unit->workshop_path): ?>
            <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-2 mx-2 bg-white rounded-xl">
                <a onclick="openModal('workshop', '<?php echo e(asset('ebooks/' . $unit->workshop_path . '/index.html')); ?>')" class="cursor-pointer block">

                    <h3 class="px-4 py-2 bg-gray-200 text-lg font-bold truncate">Workshop</h3>
                    <div class="p-4">
                        <img src="<?php echo e(asset($unit->image)); ?>" alt="Workshop"
                            class="object-cover w-full h-[250px] rounded-xl">
                    </div>
                </a>
            </div>
        <?php endif; ?>

        
        <?php if($unit->video_path): ?>
            <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-2 mx-2 bg-white rounded-xl">
                <a onclick="openModal('video', '<?php echo e($unit->video_path); ?>')" class="cursor-pointer block">
                    <h3 class="px-4 py-2 bg-gray-200 text-lg font-bold truncate">Video</h3>
                    <div class="p-4">
                        <img src="<?php echo e(asset($unit->image)); ?>" alt="Video"
                            class="object-cover w-full h-[250px] rounded-xl">
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if(!$unit->ebook_path && !$unit->workshop_path && !$unit->video_path): ?>
            <p class="m-auto text-gray-500">No learning materials available for this unit.</p>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>



<div id="ebook" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-10 hidden">
    <div class="bg-white rounded-lg shadow-lg h-[95vh] overflow-y-scroll w-[90%]">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">E-Book</h3>
            <button onclick="closeModal('ebook')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Close</button>
        </div>
        <div id="ebook-content" class="relative"></div>
    </div>
</div>

<div id="workshop" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-10 hidden">
    <div class="bg-white rounded-lg shadow-lg h-[95vh] overflow-y-scroll w-[90%]">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Workshop</h3>
            <button onclick="closeModal('workshop')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Close</button>
        </div>
        <div id="workshop-content" class="relative"></div>
    </div>
</div>

<div id="video" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-10 hidden">
    <div class="bg-white rounded-lg shadow-lg h-[95vh] overflow-y-scroll w-[90%]">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Video</h3>
            <button onclick="closeModal('video')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Close</button>
        </div>
        <div id="video-content" class="relative"></div>
    </div>
</div>

<?php $__env->startSection('page_js'); ?>
    <script>
        function openModal(type, filePath) {
            let content = '';

            if (type === 'video') {
                content = `<iframe width="100%" height="90%" src="${filePath}" frameborder="0" allowfullscreen></iframe>`;
            } else {
                content = `<iframe src="${filePath}" width="100%" height="90%" style="border:none;"></iframe>`;

            }

            document.getElementById(`${type}-content`).innerHTML = content;
            document.getElementById(type).classList.remove("hidden");
        }

        function closeModal(id) {
            document.getElementById(id).classList.add("hidden");
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u413666390/domains/pyramakerz-artifacts.com/public_html/LMS/lms_pyramakerz/resources/views/pages/student/unit/unit_content.blade.php ENDPATH**/ ?>