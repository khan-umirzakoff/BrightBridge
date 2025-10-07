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

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <br>

            <!-- Bulk Actions -->
            <div class="col-lg-12 mb-3">
                <form method="POST" action="{{ route('ai-knowledge.generate-all-embeddings') }}" style="display:inline" onsubmit="return confirm('Barcha embedding yo\'q ma\'lumotlar uchun embedding yaratilsinmi?')">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-robot"></i> Barcha Embeddinglarni Yaratish
                    </button>
                </form>
                <form method="POST" action="{{ route('ai-knowledge.seed-default') }}" style="display:inline; margin-left: 10px;" onsubmit="return confirm('Standart ma\'lumotlar yuklansinmi?')">
                    @csrf
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-seedling"></i> Standart Ma'lumotlarni Yuklash
                    </button>
                </form>
            </div>

            <br>

            <!-- Add Knowledge Form -->
            <div class="col-lg-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-star"></i> AI Muhim Ma'lumotlar</h3>
                    </div>
                    <form method="POST" action="{{ route('ai-knowledge.store') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="key">🏷️ Kalit (Qisqa nom)</label>
                                    <input type="text" name="key" id="key" class="form-control" placeholder="Masalan: Kompaniya telefoni" value="{{ old('key') }}" required>
                                    <small class="form-text text-muted">Bu ma'lumotning sarlavhasi.</small>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="category">🗂️ Kategoriya (Tanlang yoki yangi yozing)</label>
                                    <input list="category-list" name="category" id="category" class="form-control" value="{{ old('category') }}" required>
                                    <datalist id="category-list">
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}">
                                        @endforeach
                                    </datalist>
                                    <small class="form-text text-muted">Mavjud kategoriyani tanlang yoki yangisini yozing.</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="value">📝 Qiymat (To'liq ma'lumot)</label>
                                <textarea name="value" id="value" class="form-control" rows="4" placeholder="Masalan: +998 71 123 45 67" required>{{ old('value') }}</textarea>
                                <small class="form-text text-muted">AI aynan shu matnni foydalanuvchiga ko'rsatadi.</small>
                            </div>
                            <div class="row">
                                <div class="col-md-8 form-group">
                                    <label for="description">ℹ️ Izoh (Majburiy emas)</label>
                                    <input type="text" name="description" id="description" class="form-control" placeholder="Masalan: Faqat ish vaqtida qo'ng'iroq qiling" value="{{ old('description') }}">
                                    <small class="form-text text-muted">Bu ma'lumot haqida qo'shimcha eslatma (faqat siz uchun).</small>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="priority">⭐ Muhimlik (0-5)</label>
                                    <input type="number" name="priority" id="priority" class="form-control" min="0" max="5" value="{{ old('priority', 0) }}">
                                    <small class="form-text text-muted">Qancha yuqori bo'lsa, shuncha muhim.</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-save"></i> Saqlash
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <br>

            <!-- Knowledge Table -->
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Kategoriya</th>
                            <th>Kalit</th>
                            <th>Qiymat</th>
                            <th>Muhimlik</th>
                            <th>Embed</th>
                            <th>Sana</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($knowledge as $item)
                        <tr>
                            <td><span class="badge badge-info">{{ $item->category }}</span></td>
                            <td><strong>{{ $item->key }}</strong><br><small class="text-muted">{{ $item->description }}</small></td>
                            <td><div style="white-space: pre-wrap; font-size: 14px;">{{ $item->value }}</div></td>
                            <td><span class="badge badge-warning">{{ $item->priority }}</span></td>
                            <td>@if($item->embedding) <span class="badge badge-success">Embedding bor</span> @else <span class="badge badge-danger">Embedding yo'q</span> @endif</td>
                            <td>{{ $item->created_at->format('d.m.Y') }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal{{ $item->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if(!$item->embedding)
                                <form method="POST" action="{{ route('ai-knowledge.generate-embedding', $item->id) }}" style="display:inline" onsubmit="return confirm('Embedding yaratilsinmi?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" title="Embedding yaratish">
                                        <i class="fas fa-robot"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('ai-knowledge.delete', $item->id) }}" style="display:inline" onsubmit="return confirm('O\'chirmoqchimisiz?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $item->id }}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h5 class="modal-title text-white">Ma'lumotni Tahrirlash</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="POST" action="{{ route('ai-knowledge.update', $item->id) }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label for="key{{ $item->id }}">🏷️ Kalit</label>
                                                    <input type="text" name="key" id="key{{ $item->id }}" class="form-control" value="{{ $item->key }}" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="category_edit{{ $item->id }}">🗂️ Kategoriya</label>
                                                    <input list="category-list" name="category" id="category_edit{{ $item->id }}" class="form-control" value="{{ $item->category }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="value{{ $item->id }}">📝 Qiymat</label>
                                                <textarea name="value" id="value{{ $item->id }}" class="form-control" rows="4" required>{{ $item->value }}</textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-8 form-group">
                                                    <label for="description{{ $item->id }}">ℹ️ Izoh</label>
                                                    <input type="text" name="description" id="description{{ $item->id }}" class="form-control" value="{{ $item->description }}">
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label for="priority{{ $item->id }}">⭐ Muhimlik (0-5)</label>
                                                    <input type="number" name="priority" id="priority{{ $item->id }}" class="form-control" min="0" max="5" value="{{ $item->priority ?? 0 }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Yopish</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Saqlash
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 50px;">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 15px;"></i>
                                <p class="text-muted">Hali ma'lumot yo'q. Yuqoridagi formani to'ldirib, yangi bilim qo'shing.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($knowledge->hasPages())
            <div class="col-lg-12">
                {{ $knowledge->links() }}
            </div>
            @endif

        </div>
    </div>
</div>

@endsection