@extends("admin.main")

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
.settings-card {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
    border-radius: 8px;
    margin-bottom: 20px;
}
.settings-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
}
.provider-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 15px;
}
.provider-card:hover {
    border-color: #667eea;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
}
.provider-card.active {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}
.provider-logo {
    width: 50px;
    height: 50px;
    object-fit: contain;
}
.api-key-input {
    font-family: 'Courier New', monospace;
    font-size: 12px;
}
.test-btn {
    transition: all 0.3s ease;
}
.test-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.icon-wrapper {
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-right: 10px;
}
.icon-primary { background: rgba(102, 126, 234, 0.1); color: #667eea; }
.icon-success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.icon-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.icon-info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form method="POST" action="{{ route('ai-settings.update') }}" id="settings-form">
        @csrf

        <!-- AI Provider Selection -->
        <div class="settings-card">
            <div class="settings-header">
                <h4 class="mb-0">
                    <i class="fas fa-robot mr-2"></i>
                    AI Provider Configuration
                </h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Select your AI provider and configure API credentials</p>

                <div class="row">
                    <!-- OpenAI -->
                    <div class="col-md-6">
                        <div class="provider-card {{ $currentProvider === 'openai' ? 'active' : '' }}" onclick="selectProvider('openai')">
                            <input type="radio" name="ai_provider" value="openai" {{ $currentProvider === 'openai' ? 'checked' : '' }} style="display:none" id="provider-openai">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-wrapper icon-primary">
                                    <i class="fas fa-brain fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">OpenAI</h5>
                                    <small class="text-muted">GPT-4, GPT-3.5 Turbo</small>
                                </div>
                            </div>
                            <div class="openai-config" style="display: {{ $currentProvider === 'openai' ? 'block' : 'none' }}">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-key"></i> API Key
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="openai_api_key" class="form-control api-key-input"
                                               value="{{ $settings['openai'][0]->value ?? '' }}"
                                               placeholder="sk-...">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-info test-btn" type="button" onclick="testConnection('openai')">
                                                <i class="fas fa-vial"></i> Test
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <i class="fas fa-comments"></i> Chat Model
                                            </label>
                                            <input type="text" name="openai_model" class="form-control"
                                                   value="{{ $settings['openai'][1]->value ?? 'gpt-4o' }}"
                                                   placeholder="gpt-4o">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <i class="fas fa-vector-square"></i> Embedding Model
                                            </label>
                                            <input type="text" name="openai_embedding_model" class="form-control"
                                                   value="{{ $settings['openai'][2]->value ?? 'text-embedding-3-small' }}"
                                                   placeholder="text-embedding-3-small">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Google Gemini -->
                    <div class="col-md-6">
                        <div class="provider-card {{ $currentProvider === 'gemini' ? 'active' : '' }}" onclick="selectProvider('gemini')">
                            <input type="radio" name="ai_provider" value="gemini" {{ $currentProvider === 'gemini' ? 'checked' : '' }} style="display:none" id="provider-gemini">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-wrapper icon-info">
                                    <i class="fas fa-gem fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Google Gemini</h5>
                                    <small class="text-muted">Gemini 2.0 Flash, Pro</small>
                                </div>
                            </div>
                            <div class="gemini-config" style="display: {{ $currentProvider === 'gemini' ? 'block' : 'none' }}">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-key"></i> API Key
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="gemini_api_key" class="form-control api-key-input"
                                               value="{{ $settings['gemini'][0]->value ?? '' }}"
                                               placeholder="AIza...">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-info test-btn" type="button" onclick="testConnection('gemini')">
                                                <i class="fas fa-vial"></i> Test
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <i class="fas fa-comments"></i> Chat Model
                                            </label>
                                            <input type="text" name="gemini_model" class="form-control"
                                                   value="{{ $settings['gemini'][1]->value ?? 'gemini-2.0-flash-exp' }}"
                                                   placeholder="gemini-2.0-flash-exp">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <i class="fas fa-vector-square"></i> Embedding Model
                                            </label>
                                            <input type="text" name="gemini_embedding_model" class="form-control"
                                                   value="{{ $settings['gemini'][2]->value ?? 'gemini-embedding-001' }}"
                                                   placeholder="gemini-embedding-001">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Prompt Configuration -->
        <div class="settings-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <div class="icon-wrapper icon-success d-inline-flex">
                        <i class="fas fa-code"></i>
                    </div>
                    System Prompt Configuration
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Define the AI assistant's behavior and personality</p>
                <div class="form-group">
                    <label for="system_prompt">System Prompt</label>
                    <textarea name="system_prompt" id="system_prompt" class="form-control" rows="8" required>{{ $systemPrompt }}</textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i>
                        This prompt defines how the AI assistant behaves. Be clear and specific about the role, tone, and capabilities.
                    </small>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="text-right mb-4">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save"></i> Save Configuration
            </button>
        </div>
    </form>
</div>

<script>
var csrfToken = '{{ csrf_token() }}';

function selectProvider(provider) {
    // Update radio button
    document.getElementById('provider-' + provider).checked = true;

    // Update UI
    document.querySelectorAll('.provider-card').forEach(card => {
        card.classList.remove('active');
    });
    event.currentTarget.classList.add('active');

    // Show/hide configs
    document.querySelector('.openai-config').style.display = provider === 'openai' ? 'block' : 'none';
    document.querySelector('.gemini-config').style.display = provider === 'gemini' ? 'block' : 'none';
}

function togglePassword(button) {
    const input = button.closest('.input-group').querySelector('input');
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function testConnection(provider) {
    const apiKeyInput = document.querySelector(`input[name="${provider}_api_key"]`);
    const apiKey = apiKeyInput.value;

    if (!apiKey) {
        alert('Please enter API key first');
        return;
    }

    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

    fetch('{{ route("ai-settings.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            provider: provider,
            api_key: apiKey
        })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (data.success) {
            btn.classList.remove('btn-info');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="fas fa-check"></i> Connected';
            setTimeout(() => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-info');
                btn.innerHTML = originalHtml;
            }, 3000);
        } else {
            alert('Connection failed: ' + data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Connection test failed: ' + error.message);
    });
}
</script>
@endsection
