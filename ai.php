<?php
// FILE: ai.php
// This page contains all the AI-powered features like text-to-speech and text summarization.

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login if the user is not logged in.
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/db_config.php';
?>

<h1 class="text-4xl font-bold text-blue-500 text-center mb-6">AI Features</h1>
<div class="bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-4xl mx-auto">

    <!-- New Features Section with highlighted design -->
    <div class="mt-8 pt-6 border-t-2 border-gray-700">
        <h2 class="text-2xl font-bold text-gray-200 mb-4 text-center">✨ Our New Features ✨</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Text-to-Speech Card -->
            <div id="tts-card" class="bg-indigo-900 bg-opacity-40 p-6 rounded-xl shadow-lg border border-indigo-700 hover:scale-105 transition-transform duration-300 cursor-pointer" role="button" tabindex="0" aria-label="Open Text to Speech panel">
                <h3 class="text-xl font-semibold text-white mb-2">Text-to-Speech</h3>
                <p class="text-gray-300">
                    Transform any text into a natural-sounding audiobook in Hindi, English, or Gujarati languages.
                </p>
            </div>
            <!-- Image-to-Speech Card -->
            <div id="its-card" class="bg-purple-900 bg-opacity-40 p-6 rounded-xl shadow-lg border border-purple-700 hover:scale-105 transition-transform duration-300 cursor-pointer" role="button" tabindex="0" aria-label="Open Image to Speech panel">
                <h3 class="text-xl font-semibold text-white mb-2">Image-to-Speech</h3>
                <p class="text-gray-300">
                    Upload an image with text and get it read aloud in your preferred language - Hindi, English, or Gujarati.
                </p>
            </div>
            <!-- PDF-to-Speech Card -->
            <div id="pts-card" class="bg-emerald-900 bg-opacity-40 p-6 rounded-xl shadow-lg border border-emerald-700 hover:scale-105 transition-transform duration-300 cursor-pointer" role="button" tabindex="0" aria-label="Open PDF to Speech panel">
                <h3 class="text-xl font-semibold text-white mb-2">PDF-to-Speech</h3>
                <p class="text-gray-300">
                    Convert PDF documents to audio in Hindi, English, or Gujarati for listening on the go.
                </p>
            </div>
            <!-- Text Summarization Card -->
            <div id="summarize-card" class="bg-orange-900 bg-opacity-40 p-6 rounded-xl shadow-lg border border-orange-700 hover:scale-105 transition-transform duration-300 cursor-pointer" role="button" tabindex="0" aria-label="Open Text Summarization panel">
                <h3 class="text-xl font-semibold text-white mb-2">Text Summarizer</h3>
                <p class="text-gray-300">
                    Summarize long texts and get the key points read aloud in Hindi, English, or Gujarati.
                </p>
            </div>
        </div>
    </div>

    <!-- Text-to-Speech Panel -->
    <div id="tts-panel" class="hidden mt-6 bg-indigo-900 bg-opacity-20 p-6 rounded-xl border border-indigo-700">
        <h3 class="text-xl font-semibold text-white mb-4">Text-to-Speech</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Language:</label>
                <select id="tts-language" class="w-full p-2 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-indigo-500 focus:outline-none">
                    <option value="en-US">English</option>
                    <option value="hi-IN">Hindi (हिंदी)</option>
                    <option value="gu-IN">Gujarati (ગુજરાતી)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Enter text to convert to speech:</label>
                <textarea id="tts-textarea" class="w-full p-3 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-indigo-500 focus:outline-none" rows="4" placeholder="Type or paste your text here..."></textarea>
            </div>
            <div class="flex gap-3">
                <button id="tts-speak-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fa-solid fa-play mr-2"></i>Speak
                </button>
                <button id="tts-stop-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fa-solid fa-stop mr-2"></i>Stop
                </button>
              
            </div>
            <div id="tts-status" class="text-sm text-gray-400"></div>
        </div>
    </div>

    <!-- Image-to-Speech Panel -->
    <div id="its-panel" class="hidden mt-6 bg-purple-900 bg-opacity-20 p-6 rounded-xl border border-purple-700">
        <h3 class="text-xl font-semibold text-white mb-4">Image-to-Speech</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Language:</label>
                <select id="its-language" class="w-full p-2 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-purple-500 focus:outline-none">
                    <option value="en-US">English</option>
                    <option value="hi-IN">Hindi (हिंदी)</option>
                    <option value="gu-IN">Gujarati (ગુજરાતી)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Upload an image with text:</label>
                <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center hover:border-purple-500 transition-colors">
                    <input id="image-to-text-input" type="file" accept="image/*" class="hidden" />
                    <button id="image-upload-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fa-solid fa-upload mr-2"></i>Choose Image
                    </button>
                    <p class="text-gray-400 text-sm mt-2">Click to select an image file</p>
                </div>
            </div>
            <div id="image-preview" class="hidden">
                <img id="preview-img" class="max-w-full h-auto rounded-lg border border-gray-600" alt="Preview" />
            </div>
            <div class="flex gap-3">
                <button id="its-process-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200" disabled>
                    <i class="fa-solid fa-eye mr-2"></i>Extract & Speak
                </button>
                <button id="its-stop-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fa-solid fa-stop mr-2"></i>Stop
                </button>
            </div>
            <div id="its-status" class="text-sm text-gray-400"></div>
        </div>
    </div>

    <!-- PDF-to-Speech Panel -->
    <div id="pts-panel" class="hidden mt-6 bg-emerald-900 bg-opacity-20 p-6 rounded-xl border border-emerald-700">
        <h3 class="text-xl font-semibold text-white mb-4">PDF-to-Speech</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Language:</label>
                <select id="pts-language" class="w-full p-2 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-emerald-500 focus:outline-none">
                    <option value="en-US">English</option>
                    <option value="hi-IN">Hindi (हिंदी)</option>
                    <option value="gu-IN">Gujarati (ગુજરાતી)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Upload a PDF document:</label>
                <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center hover:border-emerald-500 transition-colors">
                    <input id="pdf-to-text-input" type="file" accept=".pdf" class="hidden" />
                    <button id="pdf-upload-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fa-solid fa-file-pdf mr-2"></i>Choose PDF
                    </button>
                    <p class="text-gray-400 text-sm mt-2">Click to select a PDF file</p>
                </div>
            </div>
            <div id="pdf-info" class="hidden bg-gray-700 p-3 rounded-lg">
                <p class="text-gray-300 text-sm">
                    <span class="font-medium">File:</span> <span id="pdf-filename"></span><br>
                    <span class="font-medium">Size:</span> <span id="pdf-filesize"></span>
                </p>
            </div>
            <div class="flex gap-3">
                <button id="pts-process-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200" disabled>
                    <i class="fa-solid fa-file-text mr-2"></i>Extract & Speak
                </button>
                <button id="pts-stop-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fa-solid fa-stop mr-2"></i>Stop
                </button>
            </div>
            <div id="pts-status" class="text-sm text-gray-400"></div>
        </div>
    </div>

    <!-- Text Summarization Panel -->
    <div id="summarize-panel" class="hidden mt-6 bg-orange-900 bg-opacity-20 p-6 rounded-xl border border-orange-700">
        <h3 class="text-xl font-semibold text-white mb-4">Text Summarizer</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Language:</label>
                <select id="summarize-language" class="w-full p-2 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-orange-500 focus:outline-none">
                    <option value="en-US">English</option>
                    <option value="hi-IN">Hindi (हिंदी)</option>
                    <option value="gu-IN">Gujarati (ગુજરાતી)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Enter text to summarize:</label>
                <textarea id="summarize-textarea" class="w-full p-3 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-orange-500 focus:outline-none" rows="6" placeholder="Paste your long text here for summarization..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Summary Length:</label>
                <select id="summary-length" class="w-full p-2 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-orange-500 focus:outline-none">
                    <option value="3">Short (3 sentences)</option>
                    <option value="5" selected>Medium (5 sentences)</option>
                    <option value="8">Long (8 sentences)</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button id="summarize-btn" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fa-solid fa-compress mr-2"></i>Summarize
                </button>
                <button id="summarize-speak-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200" disabled>
                    <i class="fa-solid fa-play mr-2"></i>Speak Summary
                </button>
                <button id="summarize-stop-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fa-solid fa-stop mr-2"></i>Stop
                </button>
            </div>
            <div id="summary-output" class="hidden mt-4 p-4 bg-gray-700 rounded-lg">
                <h4 class="text-white font-semibold mb-2">Summary:</h4>
                <p id="summary-text" class="text-gray-300"></p>
            </div>
            <div id="summarize-status" class="text-sm text-gray-400"></div>
        </div>
    </div>

</div>

<!-- Tesseract.js for in-browser OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js"></script>
<!-- PDF.js for PDF text extraction -->
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>

<script>
    (function() {
        // Panel toggle functionality
        const ttsCard = document.getElementById('tts-card');
        const itsCard = document.getElementById('its-card');
        const ptsCard = document.getElementById('pts-card');
        const summarizeCard = document.getElementById('summarize-card');
        const ttsPanel = document.getElementById('tts-panel');
        const itsPanel = document.getElementById('its-panel');
        const ptsPanel = document.getElementById('pts-panel');
        const summarizePanel = document.getElementById('summarize-panel');

        const allPanels = [ttsPanel, itsPanel, ptsPanel, summarizePanel];

        const togglePanel = (targetPanel, otherPanels) => {
            if (targetPanel.classList.contains('hidden')) {
                // Close other panels and open target panel
                otherPanels.forEach(panel => panel.classList.add('hidden'));
                targetPanel.classList.remove('hidden');
            } else {
                // Close target panel
                targetPanel.classList.add('hidden');
            }
        };

        if (ttsCard && ttsPanel) {
            ttsCard.addEventListener('click', () => togglePanel(ttsPanel, [itsPanel, ptsPanel, summarizePanel]));
            ttsCard.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    togglePanel(ttsPanel, [itsPanel, ptsPanel, summarizePanel]);
                }
            });
        }

        if (itsCard && itsPanel) {
            itsCard.addEventListener('click', () => togglePanel(itsPanel, [ttsPanel, ptsPanel, summarizePanel]));
            itsCard.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    togglePanel(itsPanel, [ttsPanel, ptsPanel, summarizePanel]);
                }
            });
        }

        if (ptsCard && ptsPanel) {
            ptsCard.addEventListener('click', () => togglePanel(ptsPanel, [ttsPanel, itsPanel, summarizePanel]));
            ptsCard.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    togglePanel(ptsPanel, [ttsPanel, itsPanel, summarizePanel]);
                }
            });
        }

        if (summarizeCard && summarizePanel) {
            summarizeCard.addEventListener('click', () => togglePanel(summarizePanel, [ttsPanel, itsPanel, ptsPanel]));
            summarizeCard.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    togglePanel(summarizePanel, [ttsPanel, itsPanel, ptsPanel]);
                }
            });
        }

        // Enhanced speak function with better language support
        let availableVoices = [];
        
        const loadVoices = () => {
            availableVoices = window.speechSynthesis.getVoices();
        };
        
        const speak = (text, languageCode = 'en-US') => {
            if (!('speechSynthesis' in window)) {
                alert('Text-to-Speech is not supported in this browser.');
                return;
            }
            if (!text) return;
            
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9;
            utterance.pitch = 1.0;
            utterance.lang = languageCode;
            
            // Enhanced voice selection for better language support
            let selectedVoice = null;
            
            if (languageCode === 'gu-IN') {
                // Look for Gujarati voices
                selectedVoice = availableVoices.find(v => 
                    v.lang === 'gu-IN' || 
                    v.lang === 'gu' ||
                    v.name.toLowerCase().includes('gujarati') ||
                    v.name.toLowerCase().includes('gu-in')
                );
                
                // Fallback to Hindi if Gujarati not available
                if (!selectedVoice) {
                    selectedVoice = availableVoices.find(v => 
                        v.lang === 'hi-IN' || 
                        v.lang === 'hi' ||
                        v.name.toLowerCase().includes('hindi')
                    );
                }
            } else if (languageCode === 'hi-IN') {
                // Look for Hindi voices
                selectedVoice = availableVoices.find(v => 
                    v.lang === 'hi-IN' || 
                    v.lang === 'hi' ||
                    v.name.toLowerCase().includes('hindi') ||
                    v.name.toLowerCase().includes('hi-in')
                );
            } else {
                // Look for English voices
                selectedVoice = availableVoices.find(v => 
                    v.lang === languageCode ||
                    v.lang.startsWith(languageCode.split('-')[0]) ||
                    v.name.toLowerCase().includes('english')
                );
            }
            
            if (selectedVoice) {
                utterance.voice = selectedVoice;
                console.log(`Using voice: ${selectedVoice.name} (${selectedVoice.lang})`);
            } else {
                console.log(`No specific voice found for ${languageCode}, using default`);
            }
            
            // Add error handling
            utterance.onerror = (event) => {
                console.error('Speech synthesis error:', event.error);
            };
            
            utterance.onstart = () => {
                console.log(`Started speaking in ${languageCode}`);
            };
            
            window.speechSynthesis.speak(utterance);
        };

        // Text-to-Speech functionality
        const ttsTextarea = document.getElementById('tts-textarea');
        const ttsSpeakBtn = document.getElementById('tts-speak-btn');
        const ttsStopBtn = document.getElementById('tts-stop-btn');
        const ttsPageBtn = document.getElementById('tts-page-btn');
        const ttsStatus = document.getElementById('tts-status');
        const ttsLanguage = document.getElementById('tts-language');

        const updateTtsStatus = (msg) => {
            if (ttsStatus) ttsStatus.textContent = msg || '';
        };

        if (ttsSpeakBtn && ttsTextarea) {
            ttsSpeakBtn.addEventListener('click', () => {
                const text = ttsTextarea.value.trim();
                if (!text) {
                    updateTtsStatus('Please enter some text to speak.');
                    return;
                }
                const lang = ttsLanguage ? ttsLanguage.value : 'en-US';
                updateTtsStatus('Speaking...');
                speak(text, lang);
                setTimeout(() => updateTtsStatus(''), 3000);
            });
        }

        if (ttsStopBtn) {
            ttsStopBtn.addEventListener('click', () => {
                window.speechSynthesis.cancel();
                updateTtsStatus('Stopped.');
                setTimeout(() => updateTtsStatus(''), 2000);
            });
        }

        if (ttsPageBtn) {
            ttsPageBtn.addEventListener('click', () => {
                const container = document.getElementById('about-container');
                const paragraphs = container ? Array.from(container.querySelectorAll('p')) : [];
                const text = paragraphs.map(p => p.textContent.trim()).filter(Boolean).join(' ');
                if (!text) {
                    updateTtsStatus('No text found on this page.');
                    return;
                }
                const lang = ttsLanguage ? ttsLanguage.value : 'en-US';
                updateTtsStatus('Speaking page content...');
                speak(text, lang);
            });
        }

        // Image-to-Speech functionality
        const fileInput = document.getElementById('image-to-text-input');
        const imageUploadBtn = document.getElementById('image-upload-btn');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        const itsProcessBtn = document.getElementById('its-process-btn');
        const itsStopBtn = document.getElementById('its-stop-btn');
        const itsStatus = document.getElementById('its-status');
        const itsLanguage = document.getElementById('its-language');

        let selectedFile = null;

        const updateItsStatus = (msg) => {
            if (itsStatus) itsStatus.textContent = msg || '';
        };

        if (imageUploadBtn && fileInput) {
            imageUploadBtn.addEventListener('click', () => fileInput.click());
        }

        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                selectedFile = file;
                
                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);

                // Enable process button
                if (itsProcessBtn) {
                    itsProcessBtn.disabled = false;
                }
                updateItsStatus('Image selected. Click "Extract & Speak" to process.');
            });
        }

        if (itsProcessBtn) {
            itsProcessBtn.addEventListener('click', async () => {
                if (!selectedFile) return;

                try {
                    updateItsStatus('Processing image...');
                    const selectedLang = itsLanguage ? itsLanguage.value : 'en-US';
                    const ocrLang = selectedLang.startsWith('hi') ? 'hin' : 
                                   selectedLang.startsWith('gu') ? 'guj' : 'eng';
                    
                    const { data } = await Tesseract.recognize(selectedFile, ocrLang, {
                        logger: (m) => {
                            if (m.status && m.progress != null) {
                                updateItsStatus(`${m.status} ${Math.round(m.progress * 100)}%`);
                            }
                        }
                    });
                    const text = (data && data.text) ? data.text.trim() : '';
                    if (!text) {
                        updateItsStatus('No text detected in the image.');
                        return;
                    }
                    updateItsStatus('Speaking extracted text...');
                    speak(text, selectedLang);
                    setTimeout(() => updateItsStatus(''), 3000);
                } catch (err) {
                    console.error(err);
                    updateItsStatus('Failed to process the image.');
                }
            });
        }

        if (itsStopBtn) {
            itsStopBtn.addEventListener('click', () => {
                window.speechSynthesis.cancel();
                updateItsStatus('Stopped.');
                setTimeout(() => updateItsStatus(''), 2000);
            });
        }

        // PDF-to-Speech functionality
        const pdfInput = document.getElementById('pdf-to-text-input');
        const pdfUploadBtn = document.getElementById('pdf-upload-btn');
        const pdfInfo = document.getElementById('pdf-info');
        const pdfFilename = document.getElementById('pdf-filename');
        const pdfFilesize = document.getElementById('pdf-filesize');
        const ptsProcessBtn = document.getElementById('pts-process-btn');
        const ptsStopBtn = document.getElementById('pts-stop-btn');
        const ptsStatus = document.getElementById('pts-status');
        const ptsLanguage = document.getElementById('pts-language');

        let selectedPdfFile = null;

        const updatePtsStatus = (msg) => {
            if (ptsStatus) ptsStatus.textContent = msg || '';
        };

        const formatFileSize = (bytes) => {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        };

        if (pdfUploadBtn && pdfInput) {
            pdfUploadBtn.addEventListener('click', () => pdfInput.click());
        }

        if (pdfInput) {
            pdfInput.addEventListener('change', (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                selectedPdfFile = file;
                
                // Show file info
                if (pdfFilename) pdfFilename.textContent = file.name;
                if (pdfFilesize) pdfFilesize.textContent = formatFileSize(file.size);
                if (pdfInfo) pdfInfo.classList.remove('hidden');

                // Enable process button
                if (ptsProcessBtn) {
                    ptsProcessBtn.disabled = false;
                }
                updatePtsStatus('PDF selected. Click "Extract & Speak" to process.');
            });
        }

        if (ptsProcessBtn) {
            ptsProcessBtn.addEventListener('click', async () => {
                if (!selectedPdfFile) return;

                try {
                    updatePtsStatus('Processing PDF...');
                    
                    // Read PDF file as ArrayBuffer
                    const arrayBuffer = await selectedPdfFile.arrayBuffer();
                    
                    // Load PDF document
                    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                    let fullText = '';

                    updatePtsStatus(`Extracting text from ${pdf.numPages} pages...`);

                    // Extract text from all pages
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        const textContent = await page.getTextContent();
                        const pageText = textContent.items.map(item => item.str).join(' ');
                        fullText += pageText + ' ';
                        
                        // Update progress
                        const progress = Math.round((pageNum / pdf.numPages) * 100);
                        updatePtsStatus(`Extracting text... ${progress}%`);
                    }

                    const text = fullText.trim();
                    if (!text) {
                        updatePtsStatus('No text found in the PDF.');
                        return;
                    }

                    const selectedLang = ptsLanguage ? ptsLanguage.value : 'en-US';
                    updatePtsStatus('Speaking extracted text...');
                    speak(text, selectedLang);
                    setTimeout(() => updatePtsStatus(''), 3000);
                } catch (err) {
                    console.error(err);
                    updatePtsStatus('Failed to process the PDF. Please try a different file.');
                }
            });
        }

        if (ptsStopBtn) {
            ptsStopBtn.addEventListener('click', () => {
                window.speechSynthesis.cancel();
                updatePtsStatus('Stopped.');
                setTimeout(() => updatePtsStatus(''), 2000);
            });
        }

        // Text Summarization functionality
        const summarizeTextarea = document.getElementById('summarize-textarea');
        const summarizeBtn = document.getElementById('summarize-btn');
        const summarizeSpeakBtn = document.getElementById('summarize-speak-btn');
        const summarizeStopBtn = document.getElementById('summarize-stop-btn');
        const summarizeStatus = document.getElementById('summarize-status');
        const summarizeLanguage = document.getElementById('summarize-language');
        const summaryLength = document.getElementById('summary-length');
        const summaryOutput = document.getElementById('summary-output');
        const summaryText = document.getElementById('summary-text');

        let currentSummary = '';

        const updateSummarizeStatus = (msg) => {
            if (summarizeStatus) summarizeStatus.textContent = msg || '';
        };

        // Simple text summarization function
        const summarizeText = (text, maxSentences = 5) => {
            if (!text || text.trim().length === 0) return '';
            
            // Split text into sentences
            const sentences = text.split(/[.!?]+/).map(s => s.trim()).filter(s => s.length > 0);
            
            if (sentences.length <= maxSentences) {
                return sentences.join('. ') + '.';
            }
            
            // Simple scoring based on sentence length and position
            const scoredSentences = sentences.map((sentence, index) => {
                let score = 0;
                
                // Prefer sentences in the beginning and end
                if (index === 0 || index === sentences.length - 1) score += 3;
                if (index < sentences.length * 0.3) score += 2;
                
                // Prefer medium-length sentences
                const wordCount = sentence.split(' ').length;
                if (wordCount >= 5 && wordCount <= 20) score += 2;
                
                // Look for keywords that might indicate importance
                const keywords = ['important', 'significant', 'key', 'main', 'primary', 'essential', 'critical', 'major'];
                keywords.forEach(keyword => {
                    if (sentence.toLowerCase().includes(keyword)) score += 1;
                });
                
                return { sentence, score, index };
            });
            
            // Sort by score and take top sentences
            const topSentences = scoredSentences
                .sort((a, b) => b.score - a.score)
                .slice(0, maxSentences)
                .sort((a, b) => a.index - b.index); // Maintain original order
            
            return topSentences.map(item => item.sentence).join('. ') + '.';
        };

        if (summarizeBtn && summarizeTextarea) {
            summarizeBtn.addEventListener('click', () => {
                const text = summarizeTextarea.value.trim();
                if (!text) {
                    updateSummarizeStatus('Please enter some text to summarize.');
                    return;
                }
                
                const maxSentences = parseInt(summaryLength ? summaryLength.value : '5');
                updateSummarizeStatus('Generating summary...');
                
                try {
                    currentSummary = summarizeText(text, maxSentences);
                    
                    if (summaryText) {
                        summaryText.textContent = currentSummary;
                    }
                    if (summaryOutput) {
                        summaryOutput.classList.remove('hidden');
                    }
                    if (summarizeSpeakBtn) {
                        summarizeSpeakBtn.disabled = false;
                    }
                    
                    updateSummarizeStatus('Summary generated successfully!');
                    setTimeout(() => updateSummarizeStatus(''), 3000);
                } catch (err) {
                    console.error(err);
                    updateSummarizeStatus('Failed to generate summary.');
                }
            });
        }

        if (summarizeSpeakBtn) {
            summarizeSpeakBtn.addEventListener('click', () => {
                if (!currentSummary) {
                    updateSummarizeStatus('No summary available to speak.');
                    return;
                }
                
                const selectedLang = summarizeLanguage ? summarizeLanguage.value : 'en-US';
                updateSummarizeStatus('Speaking summary...');
                speak(currentSummary, selectedLang);
                setTimeout(() => updateSummarizeStatus(''), 3000);
            });
        }

        if (summarizeStopBtn) {
            summarizeStopBtn.addEventListener('click', () => {
                window.speechSynthesis.cancel();
                updateSummarizeStatus('Stopped.');
                setTimeout(() => updateSummarizeStatus(''), 2000);
            });
        }

        // Load voices when available
        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = () => {
                // Voices are now loaded and available
                loadVoices(); // Reload voices after they are loaded
            };
        }
    })();
</script>

<?php require_once 'includes/footer.php'; ?>
