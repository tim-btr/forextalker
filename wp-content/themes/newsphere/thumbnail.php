<?php
/**
* WPSL_Thumb Class
* This class is responsible for creating a new column for editing a thumbnail from the general list of posts
* Этот класс отвечает за создание нового столбца для редактирования эскиза из общего списка записей
* @author Daniel Pataki
* @forked Yan Alexandrov
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !is_admin() ) return;

new WPSL_Thumb;
class WPSL_Thumb {

    /** Class Constructor */
    public function __construct() {
        add_action( 'init', array( $this, 'admin_list_modifications' ), 99 ) ;
        add_filter( 'admin_head', array( $this, 'add_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_print_footer_scripts', array( $this, 'add_scripts' ) );
    }

    /** Post types */
    public function post_types() {
        //$post_types = get_post_types( array( 'public' => true ) );
        $post_types = array( 'post', 'product' );
        return $post_types;
    }

    /**
    * Modify Admin Lists - Изменение Списков Администраторов
    * This function adds the custom columns and column - функция добавляет пользовательские строки и столбцы
    * content to the admin tables. Normally we would not - содержимое таблиц администратора. Нормально мы ненужно сделать это внутри функции, подключенной к init.
    * need to do this inside a function hooked to init.
    */
    public function admin_list_modifications() {
        $post_types = $this->post_types();
        foreach( $post_types as $post_type ) {
        add_filter( 'manage_edit-' . $post_type . '_columns', array( $this, 'table_head' ), 20 );
        add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
        }
    }

    /**
    * Enqueue Assets
    * Connect a standard media library to add a thumbnail through it - Подключаем стандартную медиатеку, чтобы добавить миниатюру через нее
    * @param string $page The name of the page we're on
    */
    public function enqueue_assets( $page ) {
        if ( 'edit.php' != $page ) {
        return;
        }
        wp_enqueue_media();
    }

    /**
    * Custom Column Headers
    * This function adds the custom column we need. It is added to the beginning
    * by splitting the original array - Эта функция добавляет требуемый пользовательский столбец. тот добавляется в самое начало путем разбиения исходного массива...
    * @param array $columns The columns contained in the post list
    * @return array The final array of columns to use
    */
    public function table_head( $columns ) {
        $new['_post_thumbnail'] = '<i class="dashicons dashicons-format-gallery"></i><span class="dashicons-gallerys">миниатюра</span>'; // заменим иконку столбца
        $columns = array_merge( array_slice( $columns , 0, 1 ), $new, array_slice( $columns , 1 ) );
        return $columns;
    }

    /**
    * Fill column with thumbnail
    * This function is responsible for generating the content of our columns. It
    * outputs the image, the links that launch the media uploader and the remove
    * image link - а эта функция отвечает за генерацию содержимого наших столбцов. Она выводит изображение, ссылки, запускающие загрузчик мультимедиа, и ссылку на удаление изображения
    * @param $column string
    */
    public function column_content( $column_slug, $post_id ) {
        if ( '_post_thumbnail' == $column_slug ) {
            $nonce = wp_create_nonce( "set_post_thumbnail-" . $post_id );
            $no_image = ( has_post_thumbnail( $post_id ) ) ? '' : 'no-image';
            // задаём размер миниатюры
            $fill = has_post_thumbnail( $post_id ) ? '<img width="73" height="73" class="attachment-thumbnail wp-post-image" src="' . get_the_post_thumbnail_url( $post_id, 'thumbnail' ) . '" alt="">' : '<i class="dashicons dashicons-plus"></i>';
            ?>
            <div class="wpsl-thumb <?php echo $no_image; ?>" style="opacity: 0;">
            <div class="image-box">
            <a class="choose-image" data-nonce="<?php echo $nonce; ?>" href="<?php echo get_edit_post_link( $post_id ); ?>"><?php echo $fill; ?></a>
            </div>
            <a href="<?php echo get_edit_post_link( $post_id ); ?>" data-nonce="<?php echo $nonce; ?>" class="remove-image"><i class="dashicons dashicons-no"></i></a>
            </div>
        <?
        }
    }
    
    // Add scripts only on product list
    public function add_scripts() {
    if ( current_user_can( 'edit_posts' ) && in_array( get_current_screen()->post_type, $this->post_types() ) ) {
    ?>
    <script>
    (function( $ ) {
    'use strict';
    // global wp, console
    var file_frame, post_id, nonce, wpsl_thumb;

    jQuery(document).on('click', '.choose-image', function( event ){
    post_id = $(this).parents('tr:first').attr('id').replace( 'post-', '' );
    nonce = $(this).data('nonce');
    wpsl_thumb = $(this).parents( '.wpsl-thumb' );

    /* If an instance of file_frame already exists, then we can open it rather than creating a new instance - если file_frame уже существует, то мы просто открываем его, а не создаём новый */
    if ( undefined !== file_frame ) {
    file_frame.open();
    return false;
    }
    /**
    * If we're this far, then an instance does not exist, so we need to
    * create our own - Если file_frame не существует - создаём свой собственный.
    * Here, use the wp.media library to define the settings of the Media
    * Uploader implementation by setting the title and the upload button
    * text. We're also not allowing the user to select more than one image - используйте wp.медиа-библиотека для определения настроек реализации загрузчика мультимедиа путем установки заголовка и текста
    * кнопки загрузки. Запрещаем пользователю выбирать более одного изображения
    */
    file_frame = wp.media.frames.file_frame = wp.media({
    title: '<?php _e( 'Browse or upload an image', 'plugin_name' ); ?>',
    button: {
    text: '<?php _e( 'Set thumbnail', 'plugin_name' ); ?>'
    },
    multiple: false
    });
    /* Setup an event handler for what to do when an image has been selected - Настройка обработчика событий для того, что делать, когда изображение было добавлено */
    file_frame.on( 'select', function() {
    var image_data = file_frame.state().get( 'selection' ).first().toJSON();
    var thumbnail = image_data.sizes.thumbnail;

    if( wpsl_thumb.hasClass( 'no-image' ) ) {
    wpsl_thumb.removeClass( 'no-image' );

    var link = wpsl_thumb.find('a.choose-image');
    link.html('').clone().insertAfter(link);

    var thumbnail_image = $('<img>').attr({
    width: thumbnail.width,
    height : thumbnail.height,
    class : 'attachment-thumbnail wp-post-image',
    src : thumbnail.url,
    alt : image_data.alt
    })

    wpsl_thumb.find('a.choose-image:first').html( thumbnail_image );
    } else {
    wpsl_thumb.find('.attachment-thumbnail').attr( 'src', thumbnail.url );
    }

    $.post( ajaxurl, {
    _ajax_nonce: nonce,
    post_id : post_id,
    thumbnail_id : image_data.id,
    action: 'set-post-thumbnail'
    })
    });
    // Now display the actual file_frame
    file_frame.open();

    return false;
    });

    $(document).on( 'click', '.remove-image', function() {
    wpsl_thumb = $(this).parents( '.wpsl-thumb' );
    nonce = $(this).data('nonce');
    var url = $(this).attr('href');
    var post_id = parseInt( wpsl_thumb.parents('tr:first').attr('id').replace( 'post-', '' ) );

    wpsl_thumb.addClass( 'no-image' );

    var choose_image = $('<a>').attr({
    href : url,
    'data-nonce' : nonce,
    class : 'choose-image'
    }).html("<i class='dashicons dashicons-plus'></i></a>")

    wpsl_thumb.find('.image-box').html(choose_image);

    $.post( ajaxurl, {
    _ajax_nonce: nonce,
    post_id : post_id,
    thumbnail_id : -1,
    action: 'set-post-thumbnail'
    })
    return false;
    })

    })( jQuery );
    </script>
    <?php
    }
    }
    /** Add scripts only on product list */
    public function add_styles() {
    if ( current_user_can( 'edit_posts' ) && in_array( get_current_screen()->post_type, $this->post_types() ) ) {
    echo '<style>';
    echo '
    .manage-column.column-_post_thumbnail{width:73px;/*43px*/text-align:center;}
    ._post_thumbnail.column-_post_thumbnail{overflow:visible;}
    .wpsl-thumb{position:relative;opacity:1 !important;}
    .wpsl-thumb:hover .remove-image{display:block;}
    .wpsl-thumb .image-box a{float:left;}
    .wpsl-thumb .image-box a img {max-width:73px;/*43px*/max-height:73px;/*43px*/float:left;}
    .wpsl-thumb.no-image a.choose-image{display:block;height:73px;/*43px*/width:73px;/*43px*/border:1px dashed #e1e1e1;text-align:center;background-color:#fff;padding:9px 0;box-sizing:border-box;color:#e1e1e1;
    -webkit-transition:all .1s ease-in;-moz-transition:all .1s ease-in;transition:all .1s ease-in;}
    .wpsl-thumb.no-image a.choose-image:hover {color:#0074a2;border-color:#0074a2;}
    .wpsl-thumb.no-image a.choose-image .dashicons {transition:none;-webkit-transition:none;font-size:30px;width:73px;/*30px*/height:73px;/*30px*/}
    .wpsl-thumb.no-image .remove-image{display:none;}
    .wpsl-thumb .remove-image{position:absolute;right:0;top:0;font-size:11px;color:#aaa;display:none;background-color:#ff0000;}
    .wpsl-thumb .remove-image:hover{background-color:#da0909;}
    .wpsl-thumb .remove-image .dashicons{font-size:22px;/*10px*/width:26px;/*16px*/height:26px;/*16px*/float:left;line-height:22px;/*16px*/color:#fff;}
    .dashicons-gallerys{display:none;}
    ';
    echo '</style>';
    }
    }
}