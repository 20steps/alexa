sub badbot {

    if (
      req.http.user-agent ~ "XoviBot"
    ) {
        return(synth(403, "Go away!"));
    }
}   