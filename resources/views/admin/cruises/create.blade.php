@extends('layouts.app')

@section('content')
    <div class="cruise-form-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">

                    <!-- Header della pagina -->
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="header-text">
                                <h1>Nuova Crociera</h1>
                                <p>Aggiungi una nuova crociera al sistema</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('cruises.index') }}" class="btn btn-action" style="color: white">
                                <i class="fas fa-arrow-left me-2"></i>Torna alla Lista
                            </a>
                        </div>
                    </div>

                    <!-- Card principale -->
                    <div class="form-card">
                        <div class="card-body">

                            <!-- Alert Messages -->
                            <div id="alert-container">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif
                            </div>

                            <!-- Form -->
                            <form id="cruiseForm" method="POST" action="{{ route('cruises.store') }}">
                                @csrf
                                
                                <!-- Sezione Informazioni Base -->
                                <div class="form-section">
                                    <h5 class="section-title">
                                        <i class="fas fa-info-circle me-2"></i>Informazioni Base
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ship" class="form-label required">Nome Nave</label>
                                                <input type="text" class="form-control" id="ship" name="ship" 
                                                       value="{{ old('ship') }}" required
                                                       placeholder="Es: MSC Seaside">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="line" class="form-label required">Compagnia</label>
                                                <select class="form-control" id="line" name="line" required>
                                                    <option value="">Seleziona compagnia</option>
                                                    <option value="MSC Cruises" {{ old('line') == 'MSC Cruises' ? 'selected' : '' }}>MSC Cruises</option>
                                                    <option value="Costa Crociere" {{ old('line') == 'Costa Crociere' ? 'selected' : '' }}>Costa Crociere</option>
                                                    <option value="Royal Caribbean" {{ old('line') == 'Royal Caribbean' ? 'selected' : '' }}>Royal Caribbean</option>
                                                    <option value="Norwegian Cruise Line" {{ old('line') == 'Norwegian Cruise Line' ? 'selected' : '' }}>Norwegian Cruise Line</option>
                                                    <option value="Celebrity Cruises" {{ old('line') == 'Celebrity Cruises' ? 'selected' : '' }}>Celebrity Cruises</option>
                                                    <option value="Princess Cruises" {{ old('line') == 'Princess Cruises' ? 'selected' : '' }}>Princess Cruises</option>
                                                    <option value="Holland America Line" {{ old('line') == 'Holland America Line' ? 'selected' : '' }}>Holland America Line</option>
                                                    <option value="Altra" {{ old('line') == 'Altra' ? 'selected' : '' }}>Altra</option>
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="cruise" class="form-label required">Nome Crociera</label>
                                        <input type="text" class="form-control" id="cruise" name="cruise" 
                                               value="{{ old('cruise') }}" required
                                               placeholder="Es: Mediterraneo Occidentale">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="duration" class="form-label">Durata (giorni)</label>
                                                <input type="number" class="form-control" id="duration" name="duration" 
                                                       value="{{ old('duration') }}" min="1" max="365"
                                                       placeholder="7">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="night" class="form-label">Numero Notti</label>
                                                <input type="number" class="form-control" id="night" name="night" 
                                                       value="{{ old('night') }}" min="1" max="365"
                                                       placeholder="6">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sezione Itinerario -->
                                <div class="form-section">
                                    <h5 class="section-title">
                                        <i class="fas fa-route me-2"></i>Itinerario
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="partenza" class="form-label">Data Partenza</label>
                                                <input type="date" class="form-control" id="partenza" name="partenza" 
                                                       value="{{ old('partenza') }}">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="arrivo" class="form-label">Data Arrivo</label>
                                                <input type="date" class="form-control" id="arrivo" name="arrivo" 
                                                       value="{{ old('arrivo') }}">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="from" class="form-label">Porto di Partenza</label>
                                                <input type="text" class="form-control" id="from" name="from" 
                                                       value="{{ old('from') }}"
                                                       placeholder="Es: Civitavecchia (Roma)">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="to" class="form-label">Porto di Arrivo</label>
                                                <input type="text" class="form-control" id="to" name="to" 
                                                       value="{{ old('to') }}"
                                                       placeholder="Es: Civitavecchia (Roma)">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sezione Prezzi -->
                                <div class="form-section">
                                    <h5 class="section-title">
                                        <i class="fas fa-euro-sign me-2"></i>Prezzi Cabine
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="interior" class="form-label">Cabina Interna (€)</label>
                                                <input type="number" class="form-control" id="interior" name="interior" 
                                                       value="{{ old('interior') }}" min="0" step="0.01"
                                                       placeholder="0.00">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="oceanview" class="form-label">Vista Mare (€)</label>
                                                <input type="number" class="form-control" id="oceanview" name="oceanview" 
                                                       value="{{ old('oceanview') }}" min="0" step="0.01"
                                                       placeholder="0.00">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="balcony" class="form-label">Balcone (€)</label>
                                                <input type="number" class="form-control" id="balcony" name="balcony" 
                                                       value="{{ old('balcony') }}" min="0" step="0.01"
                                                       placeholder="0.00">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="minisuite" class="form-label">Mini Suite (€)</label>
                                                <input type="number" class="form-control" id="minisuite" name="minisuite" 
                                                       value="{{ old('minisuite') }}" min="0" step="0.01"
                                                       placeholder="0.00">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="suite" class="form-label">Suite (€)</label>
                                                <input type="number" class="form-control" id="suite" name="suite" 
                                                       value="{{ old('suite') }}" min="0" step="0.01"
                                                       placeholder="0.00">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sezione Dettagli -->
                                <div class="form-section">
                                    <h5 class="section-title">
                                        <i class="fas fa-file-text me-2"></i>Dettagli Aggiuntivi
                                    </h5>
                                    
                                    <div class="form-group">
                                        <label for="details" class="form-label">Dettagli e Note</label>
                                        <textarea class="form-control" id="details" name="details" rows="4"
                                                  placeholder="Inserisci dettagli aggiuntivi, servizi inclusi, note particolari...">{{ old('details') }}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <!-- Pulsanti -->
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                    <a href="{{ route('cruises.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Annulla
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" id="loadingSpinner"></span>
                                        <span id="btnText">
                                            <i class="fas fa-save me-2"></i>Salva Crociera
                                        </span>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('admin.cruises.assets.css_cruise_form')

@section('scripts')
    @parent
    @include('admin.cruises.assets.js_cruise_form')
@endsection