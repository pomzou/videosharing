<template>
  <div class="space-y-2">
    <ul class="space-y-1 text-sm text-gray-600">
      <li class="flex items-center">
        <svg
          class="w-5 h-5 mr-2 text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 7h6m0 10H9m12-7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6z"
          />
        </svg>
        <span>
          <strong>Type:</strong>
          {{ fileExtension }}
        </span>
      </li>
      <li class="flex items-center">
        <svg
          class="w-5 h-5 mr-2 text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <span>
          <strong>Size:</strong>
          {{ fileSize }} MB
        </span>
      </li>
      <li class="flex items-center">
        <svg
          class="w-5 h-5 mr-2 text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
          />
        </svg>
        <span>
          <strong>Uploaded:</strong>
          {{ createdAt }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script>
  export default {
    props: {
      video: {
        type: Object,
        required: true,
      },
      mimeType: {
        type: String,
        default: '',
      },
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

      createdAt() {
        // コントローラーで前処理された値を使用
        return this.video.formatted_created_at || '';
      },
    },
  };
</script>
