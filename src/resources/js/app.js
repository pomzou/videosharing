<<<<<<< HEAD
import './bootstrap';
import { createApp } from 'vue';
=======

>>>>>>> d81a288 (vite install)

// グローバルコンポーネントの登録
import VideoCard from '@/components/videos/VideoCard.vue';
import VideoPreview from '@/components/videos/VideoPreview.vue';
import VideoControls from '@/components/videos/VideoControls.vue';
import ShareList from '@/components/videos/ShareList.vue';
import ShareListItem from '/components/videos/ShareListItem.vue'
import UrlSection from '@/components/videos/UrlSection.vue';
import VideoInfo from  '@/components/videos/VideoInfo.vue';

// モーダルコンポーネント
import ShareModal from '@/components/videos/modals/ShareModal.vue';
import DeleteModal from '@/components/videos/modals/DeleteModal.vue';
import ExpiryModal from '@/components/videos/modals/ExpiryModal.vue';
import ExtendShareModal from '@/components/videos/modals/ExtendShareModal.vue';

// アプリケーションの作成
const app = createApp({});

// コンポーネント登録
app.component('video-card', VideoCard);
app.component('video-preview', VideoPreview);
app.component('video-controls', VideoControls);
app.component('share-list', ShareList);
app.component('url-section', UrlSection);
app.component('share-list-item', ShareListItem);
app.component('video-info', VideoInfo);

app.component('share-modal', ShareModal);
app.component('delete-modal', DeleteModal);
app.component('expiry-modal', ExpiryModal);
app.component('extend-share-modal', ExtendShareModal);

// アプリケーションのマウント
app.mount('#app');
