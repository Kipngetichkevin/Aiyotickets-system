@extends('layouts.app')

@section('content')
<main>
  <div x-data="{ pageName: 'Edit Event' }">
    @include('partials.breadcrumb')
  </div>

  <div class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90 mb-8">Edit Event</h1>

      <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data" class="space-y-6" x-data="posterEditor()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Event Name -->
          <div class="col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Event Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $event->name) }}" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
            @error('name')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Event Type -->
          <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Event Type <span class="text-red-500">*</span>
            </label>
            <select name="category_id" id="category_id" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
              @if($event->category_id)
                <option value="{{ $event->category_id }}" selected>
                   {{ $eventTypes->firstWhere('id', $event->category_id)->name ?? 'Selected Type' }}
                </option>
              @else
                <option value="">Select type</option>
              @endif

              @foreach($eventTypes as $type)
                @if($type->id != $event->category_id)
                  <option value="{{ $type->id }}">
                    {{ $type->name }}
                  </option>
                @endif
              @endforeach
            </select>
            @error('category_id')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Event Date -->
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Event Date <span class="text-red-500">*</span>
            </label>
            <input type="datetime-local" name="date" id="date" value="{{ old('date', $event->date ? $event->date->format('Y-m-d\TH:i') : '') }}" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
            @error('date')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Event Venue -->
          <div class="col-span-2">
            <label for="venue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Venue <span class="text-red-500">*</span>
            </label>
            <input type="text" name="venue" id="venue" value="{{ old('venue', $event->venue) }}" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
            @error('venue')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Description -->
          <div class="col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Description
            </label>
            <textarea name="description" id="description" rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">{{ old('description', $event->description) }}</textarea>
            @error('description')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Event Image Editor -->
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Event Poster
            </label>
            
            <!-- Current Image Preview -->
            <div class="mb-4 relative bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden" style="max-width: 600px;">
              <div class="relative w-full h-96"> <!-- Fixed height container -->
                <img id="poster-preview" 
                     src="{{ $event->image_url ? asset('images/events/' . $event->image_url) : asset('images/placeholder.jpg') }}" 
                     alt="Event Poster" 
                     class="absolute w-full h-full object-cover rounded-lg  cursor-pointer"
                     @click="openModal = true">
                <div id="name-overlay" class="absolute text-center cursor-move select-none" 
                    style="top: {{ $event->name_y ?? 50 }}%; 
                           left: {{ $event->name_x ?? 50 }}%; 
                           transform: translate(-50%, -50%);
                           color: {{ $event->name_color ?? '#ffffff' }};
                           z-index: 10;
                           width: 100%;
                           padding: 0 20px;">
                  <h2 class="text-xl font-bold" white-space: nowrap;">{{ $event->name }}</h2>
                </div>
              </div>
            </div>
            
            <div class="flex space-x-3 mb-4">
              <button type="button" @click="openModal = true" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                Edit Poster Design
              </button>
              <button type="button" @click="generatePDF()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Download PDF
              </button>
            </div>
            
            <!-- Hidden file input for form submission -->
            <input type="file" name="image" id="image" accept="image/*" class="hidden">
            
            <!-- Hidden fields for position data -->
            <input type="hidden" name="name_x" x-model="nameX">
            <input type="hidden" name="name_y" x-model="nameY">
            <input type="hidden" name="name_color" x-model="nameColor">
            
            @error('image')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Recommended size: 1200x630 pixels (16:9 aspect ratio, max 2MB)
            </p>
          </div>
        </div>

        <div class="flex justify-between pt-6">
          <a href="{{ route('events.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-800">
            Cancel
          </a>
          <div class="space-x-4">
            <a href="{{ route('events.edit', $event->id) }}" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
              Manage Invitations
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Update Event
            </button>
          </div>
        </div>

        <!-- Poster Editor Modal -->
        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
          <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" @click="openModal = false">
              <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full" @click.stop>
              <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Event Poster</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload New Image</label>
                    <input type="file" id="poster-upload" accept="image/*" class="block w-full text-sm text-gray-500
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-md file:border-0
                      file:text-sm file:font-semibold
                      file:bg-blue-50 file:text-blue-700
                      hover:file:bg-blue-100">
                    
                    <div class="mt-4 space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name Color</label>
                        <div class="flex items-center">
                          <input type="color" id="name-color" x-model="nameColor" class="w-12 h-12">
                          <span x-text="nameColor" class="ml-2 text-sm text-gray-600 dark:text-gray-300"></span>
                        </div>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name Position</label>
                        <div class="grid grid-cols-2 gap-4">
                          <div>
                            <label class="block text-xs text-gray-500 mb-1">X: <span x-text="Math.round(nameX)"></span>%</label>
                            <input type="range" x-model="nameX" min="0" max="100" step="0.1" class="w-full">
                          </div>
                          <div>
                            <label class="block text-xs text-gray-500 mb-1">Y: <span x-text="Math.round(nameY)"></span>%</label>
                            <input type="range" x-model="nameY" min="0" max="100" step="0.1" class="w-full">
                          </div>
                        </div>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name Size</label>
                        <input type="range" x-model="nameSize" min="12" max="36" step="1" class="w-full">
                        <p class="text-xs text-gray-500">Font size: <span x-text="nameSize"></span>px</p>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Drag the name or use the sliders to position it</p>
                      </div>
                    </div>
                  </div>
                  <div class="relative w-full h-96"> <!-- Fixed height container -->
                    <div class="absolute inset-0 bg-gray-100 rounded-lg overflow-hidden">
                      <img id="poster-editor-preview" 
                           src="{{ $event->image_url ? asset('images/events/' . $event->image_url) : asset('images/placeholder.jpg') }}" 
                           alt="Poster Preview" 
                           class="w-full h-full object-cover">
                      <div id="editor-name-overlay" class="absolute text-center cursor-move select-none" 
                          x-bind:style="`top: ${nameY}%; left: ${nameX}%; transform: translate(-50%, -50%); color: ${nameColor}; z-index: 10; font-size: ${nameSize}px; width: 100%; padding: 0 20px;`"
                          draggable="false">
                        <h2 class="font-bold" white-space: nowrap;">{{ $event->name }}</h2>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" @click="savePoster()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                  Save Changes
                </button>
                <button type="button" @click="openModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white">
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('posterEditor', () => ({
    openModal: false,
    nameX: {{ $event->name_x ?? 50 }},
    nameY: {{ $event->name_y ?? 50 }},
    nameColor: '{{ $event->name_color ?? '#ffffff' }}',
    nameSize: 20, // Default font size
    isDragging: false,
    startX: 0,
    startY: 0,
    startLeft: 0,
    startTop: 0,
    
    init() {
      // Initialize draggable name in modal
      const editorNameOverlay = document.getElementById('editor-name-overlay');
      const container = document.getElementById('poster-editor-preview').parentElement;
      
      editorNameOverlay.addEventListener('mousedown', (e) => {
        this.isDragging = true;
        this.startX = e.clientX;
        this.startY = e.clientY;
        this.startLeft = this.nameX;
        this.startTop = this.nameY;
        editorNameOverlay.style.cursor = 'grabbing';
        e.preventDefault();
      });
      
      document.addEventListener('mousemove', (e) => {
        if (!this.isDragging) return;
        
        const rect = container.getBoundingClientRect();
        const newX = this.startLeft + ((e.clientX - this.startX) / rect.width * 100);
        const newY = this.startTop + ((e.clientY - this.startY) / rect.height * 100);
        
        this.nameX = Math.max(0, Math.min(100, newX));
        this.nameY = Math.max(0, Math.min(100, newY));
      });
      
      document.addEventListener('mouseup', () => {
        this.isDragging = false;
        editorNameOverlay.style.cursor = 'grab';
      });
      
      // Touch events for mobile
      editorNameOverlay.addEventListener('touchstart', (e) => {
        this.isDragging = true;
        this.startX = e.touches[0].clientX;
        this.startY = e.touches[0].clientY;
        this.startLeft = this.nameX;
        this.startTop = this.nameY;
        e.preventDefault();
      });
      
      document.addEventListener('touchmove', (e) => {
        if (!this.isDragging) return;
        
        const rect = container.getBoundingClientRect();
        const newX = this.startLeft + ((e.touches[0].clientX - this.startX) / rect.width * 100);
        const newY = this.startTop + ((e.touches[0].clientY - this.startY) / rect.height * 100);
        
        this.nameX = Math.max(0, Math.min(100, newX));
        this.nameY = Math.max(0, Math.min(100, newY));
        e.preventDefault();
      });
      
      document.addEventListener('touchend', () => {
        this.isDragging = false;
      });
      
      // Image upload preview
      document.getElementById('poster-upload')?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (event) => {
            document.getElementById('poster-editor-preview').src = event.target.result;
            document.getElementById('poster-preview').src = event.target.result;
            document.getElementById('image').files = e.target.files;
          }
          reader.readAsDataURL(file);
        }
      });
      
      // Color change handler
      document.getElementById('name-color')?.addEventListener('input', (e) => {
        this.nameColor = e.target.value;
      });
    },
    
    savePoster() {
      // Update the preview outside the modal
      const nameOverlay = document.getElementById('name-overlay');
      nameOverlay.style.left = `${this.nameX}%`;
      nameOverlay.style.top = `${this.nameY}%`;
      nameOverlay.style.color = this.nameColor;
      nameOverlay.querySelector('h2').style.fontSize = `${this.nameSize}px`;
      
      this.openModal = false;
    },
    
    generatePDF() {
      const { jsPDF } = window.jspdf;
      const posterElement = document.getElementById('poster-preview').parentElement;
      
      // Temporarily show the name overlay for capture
      const nameOverlay = document.getElementById('name-overlay');
      const originalDisplay = nameOverlay.style.display;
      nameOverlay.style.display = 'block';
      
      html2canvas(posterElement, {
        scale: 2, // Higher quality
        logging: false,
        useCORS: true,
        allowTaint: true
      }).then(canvas => {
        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const pdf = new jsPDF('landscape');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        
        // Calculate dimensions to maintain aspect ratio
        const imgWidth = canvas.width;
        const imgHeight = canvas.height;
        const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
        const imgX = (pdfWidth - imgWidth * ratio) / 2;
        const imgY = (pdfHeight - imgHeight * ratio) / 2;
        
        pdf.addImage(imgData, 'JPEG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
        pdf.save('event-poster.pdf');
        
        // Restore original display state
        nameOverlay.style.display = originalDisplay;
      });
    }
  }));
});
</script>
@endsection