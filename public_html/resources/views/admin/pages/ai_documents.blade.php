@extends("admin.main")

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

<div class="container-fluid">
    <div class="category-tab">

        <div class="tab-content">
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Error Message -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <br>

            <!-- Upload Document Form -->
            <div class="col-lg-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-upload"></i> AI uchun Hujjat Yuklash</h3>
                    </div>
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Hujjat nomi:</label>
                                <input type="text" name="title" class="form-control" placeholder="Masalan: Kompaniya qoidalari" required>
                            </div>
                            <div class="form-group">
                                <label>Kategoriya (Tanlang yoki yangi yozing):</label>
                                <input list="category-list-docs" name="category" class="form-control" placeholder="Masalan: HR, Marketing" value="">
                                <datalist id="category-list-docs">
                                    @if(isset($categories))
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}">
                                        @endforeach
                                    @endif
                                </datalist>
                                <small class="form-text text-muted">Mavjud kategoriyani tanlang yoki yangisini yozing.</small>
                            </div>
                            <div class="form-group">
                                <label>Tavsif:</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Hujjat haqida qisqacha ma'lumot"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Fayl:</label>
                                <div id="drop-zone" class="text-center p-5 border border-dashed border-primary rounded" style="cursor: pointer; background-color: #f8f9fa;">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <h5 class="text-primary">Faylni bu yerga torting yoki bosing</h5>
                                    <p class="text-muted">Faqat txt, md, pdf, doc, docx fayllar. Maksimal 10MB.</p>
                                    <input type="file" name="file" id="file-input" accept=".txt,.md,.pdf,.doc,.docx" required style="display: none;">
                                </div>
                                <div id="file-info" class="mt-2" style="display: none;">
                                    <small class="text-success">Tanlangan fayl: <span id="file-name"></span> (<span id="file-size"></span>)</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-upload"></i> Yuklash va Qayta Ishlash
                            </button>
                        </div>
                    </form>

                    <!-- Loading Modal -->
                    <div id="loading-modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-body text-center">
                                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="sr-only">Yuklanmoqda...</span>
                                    </div>
                                    <h5 class="mt-3">Fayl yuklanmoqda va embedding qayta ishlanmoqda...</h5>
                                    <p class="text-muted">Iltimos, kuting. Bu bir necha daqiqa davom etishi mumkin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <br>

            <!-- Documents Table -->
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="15%">Nomi</th>
                            <th width="10%">Kategoriya</th>
                            <th width="20%">Fayl</th>
                            <th width="8%">Hajmi</th>
                            <th width="10%">Embed</th>
                            <th width="12%">Sana</th>
                            <th width="10%">Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td>{{ $doc->title }}</td>
                            <td>{{ $doc->category }}</td>
                            <td>{{ $doc->file_name }}</td>
                            <td>{{ number_format($doc->file_size / 1024, 1) }} KB</td>
                            <td>{{ $doc->embedding ? 'Ha' : 'Yo\'q' }}</td>
                            <td>{{ date('d.m.Y H:i', strtotime($doc->created_at)) }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteDocument({{ $doc->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 50px;">
                                <i class="fas fa-file" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 15px;"></i>
                                <p class="text-muted">Hali hujjat yo'q. Yuqorida fayl yuklang.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($documents->hasPages())
            <div class="col-lg-12">
                {{ $documents->links() }}
            </div>
            @endif

        </div>
    </div>
</div>

<script>
var csrfToken = '{{ csrf_token() }}';

function showLoading() {
    $('#loading-modal').modal('show');
}

function startPolling(documentId) {
    // Start polling
    const interval = setInterval(() => {
        fetch(`/admin/ai-documents/progress/${documentId}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.progress.progress >= 100) {
                clearInterval(interval);
                $('#loading-modal').modal('hide');
                alert('Fayl muvaffaqiyatli yuklandi va embedding qayta ishlandi!');
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Progress error:', error);
        });
    }, 2000); // Poll every 2 seconds
}

function hideLoading() {
    $('#loading-modal').modal('hide');
}

document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const uploadForm = document.getElementById('uploadForm');
    let selectedFile = null;

    // Click to open file dialog
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        selectedFile = e.target.files[0];
        handleFile(selectedFile);
    });

    // Drag and drop events
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-light');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-light');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-light');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            selectedFile = files[0];
            // Create a new DataTransfer to properly set files
            const dt = new DataTransfer();
            for (let file of files) {
                dt.items.add(file);
            }
            fileInput.files = dt.files;
            handleFile(selectedFile);
        }
    });

    // Form submission
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!uploadForm.title.value.trim()) {
            alert('Iltimos, sarlavha kiriting!');
            return;
        }

        if (!selectedFile) {
            alert('Iltimos, fayl tanlang!');
            return;
        }

        // Show loading immediately
        showLoading();

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('title', uploadForm.title.value);
        formData.append('category', uploadForm.category.value);
        formData.append('description', uploadForm.description.value);
        formData.append('file', selectedFile);

        // Submit via AJAX
        fetch('{{ route("ai-documents.upload") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                uploadForm.reset();
                fileInfo.style.display = 'none';
                // Start polling for completion
                startPolling(data.document_id);
            } else {
                hideLoading();
                alert('Xatolik: ' + (data.error || 'Noma\'lum xatolik'));
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            hideLoading();
            alert('Yuklashda xatolik yuz berdi');
        });
    });

    function handleFile(file) {
        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'block';
        } else {
            fileInfo.style.display = 'none';
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    window.deleteDocument = function(id) {
        if (confirm("Hujjatni o'chirishni tasdiqlaysizmi?")) {
            fetch(`/admin/ai-documents/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Hujjat o'chirildi");
                    window.location.reload();
                } else {
                    alert('Xatolik: ' + (data.error || 'Noma\'lum xatolik'));
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert("O'chirishda xatolik");
            });
        }
    };
});
</script>

@endsection