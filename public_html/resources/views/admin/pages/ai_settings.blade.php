@extends("admin.main")

@section('head')
{{-- This section is intentionally left blank to demonstrate that page-specific styles can be added here. --}}
@endsection

@section('content')
<div class="container-fluid ai-section-body">

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <svg class="ai-icon" style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
             <svg class="ai-icon" style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form method="POST" action="{{ route('ai-settings.update') }}" id="settings-form">
        @csrf

        {{-- AI Provider Selection Card --}}
        <div class="ai-card">
            <div class="ai-card-header">
                <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                <h5 class="ai-card-title">AI Provider Configuration</h5>
            </div>
            <div class="ai-card-body">
                <p class="text-muted mb-4">Select your AI provider and configure its API credentials. The selected provider will be used for all AI-powered features.</p>

                <div class="ai-form-group">
                    <label class="ai-form-label" for="ai_provider_dropdown">AI Provider</label>
                    <div class="ai-provider-dropdown">
                        <input type="hidden" name="ai_provider" id="ai_provider_input" value="{{ $currentProvider }}">
                        <button type="button" class="ai-provider-dropdown-btn" id="ai_provider_dropdown_btn">
                            <span id="selected_provider_text">
                                {{-- This will be populated by JS --}}
                            </span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="ai-provider-dropdown-content" id="ai_provider_dropdown_content">
                            <div class="ai-provider-dropdown-item" data-provider="openai">
                                <svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"><path fill="currentColor" d="M352 256c0 22.2-1.2 43.6-3.3 64H147.3c-2.1-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.1 20.4 3.3 41.8 3.3 64zm28.8-96H115.2C69.3 160 32 204.2 32 256s37.3 96 83.2 96h233.6c45.9 0 83.2-44.2 83.2-96s-37.3-96-83.2-96zM496 256c0-114.9-111.6-208-248-208S0 141.1 0 256s111.6 208 248 208 248-93.1 248-208zM100.3 400H208v48c0 17.7-14.3 32-32 32h-48c-17.7 0-32-14.3-32-32v-48zm295.4 0H288v48c0 17.7 14.3 32 32 32h48c17.7 0 32-14.3 32-32v-48z"/></svg>
                                OpenAI
                            </div>
                            <div class="ai-provider-dropdown-item" data-provider="gemini">
                               <svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M152.3 34.6c-5.1-12.8-20.5-12.8-25.6 0L5.3 394.6C-1.5 412.8 13 432 32 432h384c19 0 33.5-19.2 26.7-37.4L295.7 34.6c-5.1-12.8-20.5-12.8-25.6 0l-117.8 360zM224 288c-13.3 0-24 10.7-24 24s10.7 24 24 24 24-10.7 24-24-10.7-24-24-24zm-32-80c0-17.7 14.3-32 32-32s32 14.3 32 32v64c0 17.7-14.3 32-32 32s-32-14.3-32-32v-64z"/></svg>
                                Google Gemini
                            </div>
                        </div>
                    </div>
                </div>

                {{-- OpenAI Config Section --}}
                <div id="openai-config" class="ai-config-section">
                    <div class="form-group">
                        <label class="ai-form-label">API Key</label>
                        <div class="input-group">
                            <input type="password" name="openai_api_key" class="ai-form-control" value="{{ $settings['openai'][0]->value ?? '' }}" placeholder="sk-...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-info" type="button" onclick="testConnection('openai', this)">Test</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Chat Model</label><input type="text" name="openai_model" class="ai-form-control" value="{{ $settings['openai'][1]->value ?? 'gpt-4o' }}" placeholder="gpt-4o"></div></div>
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Embedding Model</label><input type="text" name="openai_embedding_model" class="ai-form-control" value="{{ $settings['openai'][2]->value ?? 'text-embedding-3-small' }}" placeholder="text-embedding-3-small"></div></div>
                    </div>
                </div>

                {{-- Gemini Config Section --}}
                <div id="gemini-config" class="ai-config-section">
                    <div class="form-group">
                        <label class="ai-form-label">API Key</label>
                        <div class="input-group">
                            <input type="password" name="gemini_api_key" class="ai-form-control" value="{{ $settings['gemini'][0]->value ?? '' }}" placeholder="AIza...">
                             <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-info" type="button" onclick="testConnection('gemini', this)">Test</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Chat Model</label><input type="text" name="gemini_model" class="ai-form-control" value="{{ $settings['gemini'][1]->value ?? 'gemini-2.0-flash-exp' }}" placeholder="gemini-2.0-flash-exp"></div></div>
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Embedding Model</label><input type="text" name="gemini_embedding_model" class="ai-form-control" value="{{ $settings['gemini'][2]->value ?? 'gemini-embedding-001' }}" placeholder="gemini-embedding-001"></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Prompt Card --}}
        <div class="ai-card">
            <div class="ai-card-header">
                <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                <h5 class="ai-card-title">System Prompt</h5>
            </div>
            <div class="ai-card-body">
                <p class="text-muted">Define the AI assistant's behavior and personality. This prompt sets the context for all conversations.</p>
                <div class="ai-form-group">
                    <textarea name="system_prompt" id="system_prompt" class="ai-form-control" rows="8" required>{{ $systemPrompt }}</textarea>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="text-right mb-4">
            <button type="submit" class="ai-btn ai-btn-primary">
                <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Save Configuration
            </button>
        </div>
    </form>
</div>

<script>
var csrfToken = '{{ csrf_token() }}';

document.addEventListener('DOMContentLoaded', function () {
    const providerInput = document.getElementById('ai_provider_input');
    const dropdownButton = document.getElementById('ai_provider_dropdown_btn');
    const dropdownContent = document.getElementById('ai_provider_dropdown_content');
    const selectedProviderText = document.getElementById('selected_provider_text');
    const configSections = {
        openai: document.getElementById('openai-config'),
        gemini: document.getElementById('gemini-config'),
    };
    const providerItems = document.querySelectorAll('.ai-provider-dropdown-item');

    function selectProvider(provider) {
        providerInput.value = provider;
        const selectedItem = document.querySelector(`.ai-provider-dropdown-item[data-provider="${provider}"]`);
        selectedProviderText.innerHTML = selectedItem.innerHTML;
        for (const key in configSections) {
            configSections[key].classList.toggle('active', key === provider);
        }
        dropdownContent.style.display = 'none';
    }

    selectProvider(providerInput.value);

    dropdownButton.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
    });

    providerItems.forEach(item => {
        item.addEventListener('click', function () {
            selectProvider(this.getAttribute('data-provider'));
        });
    });

    window.addEventListener('click', (e) => {
        if (!dropdownButton.contains(e.target)) {
            dropdownContent.style.display = 'none';
        }
    });
});

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

function testConnection(provider, button) {
    const apiKeyInput = document.querySelector(`input[name="${provider}_api_key"]`);
    const apiKey = apiKeyInput.value;

    if (!apiKey) {
        alert('Please enter an API key first.');
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('{{ route("ai-settings.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ provider: provider, api_key: apiKey })
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        if (data.success) {
            button.classList.remove('btn-info', 'btn-danger');
            button.classList.add('btn-success');
            button.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                button.classList.remove('btn-success');
                button.classList.add('btn-info');
                button.innerHTML = originalHtml;
            }, 3000);
        } else {
            button.classList.remove('btn-info');
            button.classList.add('btn-danger');
            button.innerHTML = '<i class="fas fa-times"></i>';
            alert('Connection failed: ' + data.message);
            setTimeout(() => {
                button.classList.remove('btn-danger');
                button.classList.add('btn-info');
                button.innerHTML = originalHtml;
            }, 5000);
        }
    })
    .catch(error => {
        button.disabled = false;
        button.innerHTML = originalHtml;
        alert('Connection test failed: ' + error.message);
    });
}
</script>
@endsection