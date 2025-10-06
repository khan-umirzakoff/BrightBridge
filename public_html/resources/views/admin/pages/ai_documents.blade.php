@extends("admin.main")

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
                    <form method="POST" action="{{ route('ai-documents.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Hujjat nomi:</label>
                                <input type="text" name="title" class="form-control" placeholder="Masalan: Kompaniya qoidalari" required>
                            </div>
                            <div class="form-group">
                                <label>Kategoriya:</label>
                                <input type="text" name="category" class="form-control" placeholder="Masalan: HR, Marketing" value="general">
                            </div>
                            <div class="form-group">
                                <label>Tavsif:</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Hujjat haqida qisqacha ma'lumot"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Fayl:</label>
                                <input type="file" name="file" class="form-control" accept=".txt,.md,.pdf,.doc,.docx" required>
                                <small class="form-text text-muted">Faqat txt, md, pdf, doc, docx fayllar. Maksimal 10MB.</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-upload"></i> Yuklash va Qayta Ishlash
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <br>

            <!-- Documents Table -->
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="20%">Nomi</th>
                            <th width="15%">Kategoriya</th>
                            <th width="25%">Fayl</th>
                            <th width="10%">Hajmi</th>
                            <th width="15%">Sana</th>
                            <th width="15%">Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td>{{ $doc->title }}</td>
                            <td>{{ $doc->category }}</td>
                            <td>{{ $doc->file_name }}</td>
                            <td>{{ number_format($doc->file_size / 1024, 1) }} KB</td>
                            <td>{{ $doc->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <form method="POST" action="{{ route('ai-documents.delete', $doc->id) }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('O\'chirmoqchimisiz?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 50px;">
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

@endsection
