<template>
  <div class="relative pt-[56.25%] bg-gray-100 rounded-lg overflow-hidden">
    <!-- 動画の場合 -->
    <video
      v-if="fileType === 'video'"
      :id="`video-${videoId}`"
      class="absolute top-0 left-0 w-full h-full object-cover"
      controls
      preload="metadata"
      @play="checkExpiry"
    >
      <source v-if="hasPreviewUrl" :src="previewUrl" :type="mimeType" />
    </video>

    <!-- 画像の場合 -->
    <img
      v-else-if="fileType === 'image'"
      :src="previewUrl"
      :alt="video.title || ''"
      class="absolute top-0 left-0 w-full h-full object-contain"
    />

    <!-- 音声の場合 -->
    <div
      v-else-if="fileType === 'audio'"
      class="absolute top-0 left-0 w-full h-full flex items-center justify-center bg-gray-800"
    >
      <audio controls class="w-3/4">
        <source :src="previewUrl" :type="mimeType" />
      </audio>
    </div>

    <!-- PDFの場合 -->
    <iframe
      v-else-if="fileType === 'pdf'"
      :src="previewUrl"
      class="absolute top-0 left-0 w-full h-full"
    ></iframe>

    <!-- テキストの場合 -->
    <div
      v-else-if="fileType === 'text' && isOwner"
      class="absolute top-0 left-0 w-full h-full overflow-auto p-4 bg-white"
    >
      <pre class="text-sm whitespace-pre-wrap">{{ previewText }}</pre>
    </div>

    <!-- その他のファイルタイプ -->
    <div
      v-else
      class="absolute top-0 left-0 w-full h-full flex items-center justify-center"
    >
      <div class="text-center">
        <div class="flex justify-center mb-4">
          <!-- ファイルタイプに応じたアイコン -->
          <svg
            class="w-16 h-16 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              v-if="fileType === 'document'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
            <path
              v-else-if="fileType === 'spreadsheet'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
            />
            <path
              v-else-if="fileType === 'archive'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
            />
          </svg>
        </div>
        <p class="text-gray-500">{{ fileExtension }} File</p>
        <p class="text-sm text-gray-400">{{ fileSize }} MB</p>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      video: {
        type: Object,
        required: true,
      },
      previewUrl: {
        type: String,
        default: '',
      },
      mimeType: {
        type: String,
        default: '',
      },
      isOwner: {
        type: Boolean,
        default: false,
      },
      videoId: {
        type: [Number, String],
        required: true,
      },
    },

    data() {
      return {
        videoVisible: true,
        previewText: '',
      };
    },

    computed: {
      fileType() {
        // MIMEタイプからファイルタイプを判断
        const mime = this.mimeType.toLowerCase();
        if (mime.startsWith('video/')) return 'video';
        if (mime.startsWith('image/')) return 'image';
        if (mime.startsWith('audio/')) return 'audio';
        if (mime === 'application/pdf') return 'pdf';
        if (mime.startsWith('text/')) return 'text';

        // 拡張子から判断（バックアップ）
        const ext = this.fileExtension.toLowerCase();
        if (['doc', 'docx', 'odt'].includes(ext)) return 'document';
        if (['xls', 'xlsx', 'csv'].includes(ext)) return 'spreadsheet';
        if (['zip', 'rar', 'tar', 'gz'].includes(ext)) return 'archive';

        return 'file'; // デフォルト
      },

      fileExtension() {
        return this.video.original_name
          ? this.video.original_name.split('.').pop().toUpperCase()
          : '';
      },

      fileSize() {
        return this.video.file_size
          ? (this.video.file_size / 1024 / 1024).toFixed(2)
          : '0';
      },

      hasPreviewUrl() {
        return this.isOwner || !!this.previewUrl;
      },
    },

    methods: {
      checkExpiry() {
        if (!this.isOwner) {
          const timerDiv = document.getElementById(`timer-${this.videoId}`);
          const hasExpired = timerDiv
            ? timerDiv.classList.contains('hidden')
            : true;

          if (hasExpired) {
            const videoElement = document.getElementById(
              `video-${this.videoId}`
            );
            if (videoElement) {
              videoElement.pause();
            }
            this.$emit(
              'expired',
              'This video link has expired. Please generate a new download link.'
            );
          }
        }
      },
    },
  };
</script>
