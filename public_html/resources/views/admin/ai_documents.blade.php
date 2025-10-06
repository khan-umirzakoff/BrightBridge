@extends('admin.layouts.app')

@section('title', 'AI Hujjatlar Boshqaruvi')

@section('content')
<div class=\"container-fluid\">
    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h3 class=\"card-title\">AI Bilim Bazasi - Hujjatlar</h3>
                    <button class=\"btn btn-primary btn-sm float-right\" data-toggle=\"modal\" data-target=\"#uploadModal\">
                        <i class=\"fas fa-plus\"></i> Yangi Hujjat
                    </button>
                </div>
                <div class=\"card-body\">
                    <div class=\"table-responsive\">
                        <table class=\"table table-bordered table-striped\" id=\"documentsTable\">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sarlavha</th>
                                    <th>Kategoriya</th>
                                    <th>Tavsif</th>
                                    <th>Fayl</th>
                                    <th>Sana</th>
                                    <th>Amallar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class=\"modal fade\" id=\"uploadModal\" tabindex=\"-1\" role=\"dialog\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\">Yangi Hujjat Yuklash</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\">
                    <span>&times;</span>
                </button>
            </div>
            <form id=\"uploadForm\" enctype=\"multipart/form-data\">
                <div class=\"modal-body\">
                    <div class=\"form-group\">
                        <label for=\"title\">Sarlavha *</label>
                        <input type=\"text\" class=\"form-control\" id=\"title\" name=\"title\" required>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"category\">Kategoriya</label>
                        <select class=\"form-control\" id=\"category\" name=\"category\">
                            <option value=\"\">Kategoriya tanlang</option>
                            <option value=\"guide\">Qo'llanma</option>
                            <option value=\"policy\">Siyosat</option>
                            <option value=\"faq\">FAQ</option>
                            <option value=\"training\">O'quv materiali</option>
                            <option value=\"procedure\">Jarayon</option>
                            <option value=\"other\">Boshqa</option>
                        </select>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"description\">Tavsif</label>
                        <textarea class=\"form-control\" id=\"description\" name=\"description\" rows=\"3\"></textarea>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"file\">Fayl *</label>
                        <input type=\"file\" class=\"form-control-file\" id=\"file\" name=\"file\" 
                               accept=\".txt,.md,.pdf,.doc,.docx\" required>
                        <small class=\"form-text text-muted\">
                            Qo'llab-quvvatlanadigan formatlar: TXT, MD, PDF, DOC, DOCX (max 10MB)
                        </small>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"content\">Matn Mazmuni (ixtiyoriy)</label>
                        <textarea class=\"form-control\" id=\"content\" name=\"content\" rows=\"5\" 
                                placeholder=\"Agar fayl matnini o'qib bo'lmasa, bu yerga qo'lda kiriting...\"></textarea>
                    </div>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Bekor qilish</button>
                    <button type=\"submit\" class=\"btn btn-primary\">
                        <i class=\"fas fa-upload\"></i> Yuklash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Load documents on page load
    loadDocuments();
    
    // Upload form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '{{ route(\"ai-documents.upload\") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#uploadModal').modal('hide');
                    $('#uploadForm')[0].reset();
                    loadDocuments();
                    toastr.success('Hujjat muvaffaqiyatli yuklandi!');
                } else {
                    toastr.error(response.error || 'Xatolik yuz berdi');
                }
            },
            error: function(xhr) {
                var error = 'Yuklashda xatolik';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    error = xhr.responseJSON.error;
                }
                toastr.error(error);
            }
        });
    });
    
    function loadDocuments() {
        $.get('{{ route(\"ai-documents.list\") }}', function(response) {
            if (response.success) {
                var tbody = $('#documentsTable tbody');
                tbody.empty();
                
                response.documents.forEach(function(doc) {
                    var row = `
                        <tr>
                            <td>${doc.id}</td>
                            <td><strong>${doc.title}</strong></td>
                            <td>${doc.category || '-'}</td>
                            <td>${doc.description ? doc.description.substring(0, 100) + '...' : '-'}</td>
                            <td>
                                <small>${doc.file_name}</small><br>
                                <span class=\"badge badge-info\">${formatFileSize(doc.file_size)}</span>
                            </td>
                            <td>${new Date(doc.created_at).toLocaleDateString()}</td>
                            <td>
                                <button class=\"btn btn-danger btn-sm\" onclick=\"deleteDocument(${doc.id})\">
                                    <i class=\"fas fa-trash\"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
        });
    }
    
    window.deleteDocument = function(id) {
        if (confirm("Hujjatni o'chirishni tasdiqlaysizmi?")) {
            $.ajax({
                url: `/admin/ai-documents/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadDocuments();
                        toastr.success("Hujjat o'chirildi");
                    } else {
                        toastr.error(response.error || 'Xatolik yuz berdi');
                    }
                },
                error: function() {
                    toastr.error("O'chirishda xatolik");
                }
            });
        }
    };
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endsection