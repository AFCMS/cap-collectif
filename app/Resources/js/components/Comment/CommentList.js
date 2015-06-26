import Comment from './Comment';


var CommentList = React.createClass({
    mixins: [ReactIntl.IntlMixin],

    render() {
        return (
            <ul className="media-list  opinion__list">
                {
                    this.props.comments.map((comment) => {
                        return <Comment key={comment.id} comment={comment} can_report={this.props.can_report} />;
                    })
                }
            </ul>
        );
    },



});

export default CommentList;
