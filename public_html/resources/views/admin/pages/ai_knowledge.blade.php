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

            <!-- Add Knowledge Form -->
            <div class="col-lg-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-robot"></i> AI ga O'rgatish Uchun Ma'lumot Qo'shish</h3>
                    </div>
                    <form method="POST" action="{{ route('ai-knowledge.store') }}">
                        @csrf
                        <input type="hidden" name="category" value="general">
                        <input type="hidden" name="key" value="knowledge">
                        <div class="card-body">
                            <div class="form-group">
                                <label>💡 Ma'lumot matni (AI o'qiydi va eslab qoladi):</label>
                                <textarea name="value" class="form-control" rows="12" placeholder="Masalan:

📞 Telefon raqamimiz: +998 71 123 45 67
⏰ Ish vaqti: Dushanba-Juma 9:00-18:00
📧 Email: info@jobcare.uz
📍 Manzil: Toshkent shahar, Amir Temur ko'chasi 123
✨ Xizmatlar: Bepul ish e'lonlari, CV tahlil, intervyu tayyorlovi

Qo'shimcha ma'lumotlar..." required></textarea>
                                <small class="form-text text-muted">
                                    ℹ️ AI shu matnni o'qiydi va foydalanuvchi savolariga javob berishda ishlatadi
                                </small>
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
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="75%">Ma'lumot</th>
                            <th width="15%">Sana</th>
                            <th width="10%">Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($knowledge as $item)
                        <tr>
                            <td>
                                <div style="white-space: pre-wrap; font-size: 14px; line-height: 1.6;">{{ $item->value }}</div>
                            </td>
                            <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $item->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('ai-knowledge.delete', $item->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('O\'chirmoqchimisiz?')">
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
                                        <input type="hidden" name="category" value="general">
                                        <input type="hidden" name="key" value="knowledge">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Ma'lumot:</label>
                                                <textarea name="value" class="form-control" rows="15" required>{{ $item->value }}</textarea>
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
                            <td colspan="3" class="text-center" style="padding: 50px;">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 15px;"></i>
                                <p class="text-muted">Hali ma'lumot yo'q. Yuqorida matn kiritib saqlang.</p>
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
